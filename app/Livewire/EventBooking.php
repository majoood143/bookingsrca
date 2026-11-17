<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPrivateEvents;
use App\Livewire\Concerns\HandlesEventBookingFlow;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Services\CCAvenueService;
use App\Services\NboService;
use App\Services\ThawaniService;
use Exception;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EventBooking extends Component
{
    use GuardsPrivateEvents, HandlesEventBookingFlow;

    // Payment
    #[Locked]
    public string $selectedPaymentMethod = '';

    #[Locked]
    public array $enabledGateways = [];

    public function mount()
    {
        $this->guardEventAccess($this->event);

        if (! $this->passwordRequired) {
            $this->afterEventUnlocked();
        }
    }

    protected function afterEventUnlocked(): void
    {
        $this->loadBookingFieldSettings();

        $this->enabledGateways = $this->resolveEnabledGateways();
        $this->selectedPaymentMethod = $this->enabledGateways[0] ?? 'free';
    }

    /**
     * Reads the enabled_gateways JSON setting, falling back to the legacy
     * single active_gateway value for installs that haven't saved the
     * payment gateways settings page since the multi-gateway option was
     * introduced.
     */
    protected function resolveEnabledGateways(): array
    {
        $stored = BookingSetting::get('enabled_gateways');
        $decoded = $stored ? json_decode($stored, true) : null;

        if (is_array($decoded) && ! empty($decoded)) {
            return array_values($decoded);
        }

        return [(string) BookingSetting::get('active_gateway', 'free')];
    }

    // Called when the customer picks a different payment method in step 6
    // (only rendered when more than one gateway is enabled).
    public function selectGateway(string $gateway): void
    {
        if (in_array($gateway, $this->enabledGateways, true)) {
            $this->selectedPaymentMethod = $gateway;
        }
    }

    // Step 6 "Confirm / Pay" — create the booking and handle the selected gateway.

    public function submitBooking()
    {
        // Each pending booking holds its seats until the expiry sweep, so cap how many
        // one visitor can open — otherwise a script can hold every seat of a slot.
        // Per session, with a high per-IP ceiling: a crowd at a venue shares one IP.
        $limits = ['booking-session:'.session()->getId() => 5, 'booking-ip:'.request()->ip() => 60];

        foreach ($limits as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                session()->flash('error', __('Too many bookings started. Please try again in a few minutes.'));

                return;
            }
        }

        // Livewire exposes every public method as a directly-callable endpoint,
        // so this can be invoked without ever having passed through step 5's
        // goToPaymentStep() — re-validate here rather than trusting that flow.
        $this->validate();

        // The password prompt is only UI: the session is what proves the event was unlocked.
        if ($this->event->status !== 'published' && ! auth()->check() && ! $this->eventIsUnlocked($this->event)) {
            abort(403);
        }

        try {
            $booking = $this->persistBooking(function (float $ticketPrice, float $servicesPrice, float $discountAmount) {
                $isFree = ($ticketPrice + $servicesPrice - $discountAmount) <= 0;

                // Re-read from settings: a method the admin hasn't enabled (e.g. "cash")
                // would fall through to the confirm-immediately branch below.
                if (! $isFree && ! in_array($this->selectedPaymentMethod, $this->resolveEnabledGateways(), true)) {
                    throw new Exception(__('Payment gateway is not configured. Please contact support.'));
                }

                return [
                    'source' => 'online',
                    'payment_method' => $isFree ? 'free' : $this->selectedPaymentMethod,
                ];
            });
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        foreach (array_keys($limits) as $key) {
            RateLimiter::hit($key, 600);
        }

        $isFree = $booking->payment_method === 'free';

        // ── Route by gateway ────────────────────────────────────────────
        if (! $isFree && $this->selectedPaymentMethod === 'thawani') {
            return $this->redirectToThawani($booking);
        }

        if (! $isFree && $this->selectedPaymentMethod === 'nbo') {
            return $this->redirectToNbo($booking);
        }

        if (! $isFree && $this->selectedPaymentMethod === 'ccavenue') {
            return $this->redirectToCcavenue($booking);
        }

        // Cash / Free — confirm immediately (confirm() handles all emails)
        $booking->update(['payment_status' => 'paid']);
        $booking->confirm();

        session()->flash('success', __('Booking confirmed! Check your email for details.'));

        return redirect()->route('booking.success', $booking->booking_reference);
    }

    // ── Thawani redirect ─────────────────────────────────────────────────────

    protected function redirectToThawani(Booking $booking)
    {
        try {
            $thawani = app(ThawaniService::class);

            if (! $thawani->isConfigured()) {
                throw new Exception(__('Payment gateway is not configured. Please contact support.'));
            }

            $ticketTypesById = $this->loadedTicketTypes()->keyBy('id');
            $extraServicesById = $this->loadedExtraServices()->keyBy('id');

            // Build product line(s) — one line per distinct ticket type selected
            $products = [];
            foreach ($this->ticketQuantities as $typeId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $ticketType = $ticketTypesById[$typeId] ?? null;
                if (! $ticketType) {
                    continue;
                }
                $products[] = [
                    'name' => $ticketType->getTranslation('name', 'en'),
                    'quantity' => $qty,
                    'unit_amount' => $thawani->toBasisa((float) $ticketType->price),
                ];
            }

            // Add extra-service lines
            foreach ($this->ticketTypeServices as $typeId => $serviceCounts) {
                $qty = $this->ticketQuantities[$typeId] ?? 0;
                if ($qty <= 0 || empty($serviceCounts)) {
                    continue;
                }
                foreach ($serviceCounts as $serviceId => $count) {
                    if ($count <= 0) {
                        continue;
                    }
                    $service = $extraServicesById[$serviceId] ?? null;
                    if (! $service) {
                        continue;
                    }
                    $products[] = [
                        'name' => $service->getTranslation('name', 'en'),
                        'quantity' => $count,
                        'unit_amount' => $thawani->toBasisa((float) $service->price),
                    ];
                }
            }

            // Thawani charges the sum of the product lines rather than
            // booking->total_price, so a promo discount has to be folded into
            // those lines to keep the amount charged in sync with the booking.
            $discountBasisa = $thawani->toBasisa((float) $booking->discount_amount);
            if ($discountBasisa > 0) {
                $products = $this->applyDiscountToProductLines($products, $discountBasisa);
            }

            $response = $thawani->createSession([
                'client_reference_id' => $booking->booking_reference,
                'products' => $products,
                'success_url' => route('payment.callback').'?reference='.$booking->booking_reference,
                'cancel_url' => route('payment.cancel').'?reference='.$booking->booking_reference,
                'metadata' => ['booking_id' => $booking->id],
            ], $booking);

            $sessionId = $response['data']['session_id'] ?? null;

            if (! $sessionId) {
                throw new Exception(__('Failed to create payment session. Please try again.'));
            }

            $booking->update(['payment_session_id' => $sessionId]);

            return redirect()->away($thawani->getCheckoutUrl($sessionId));

        } catch (Exception $e) {
            // Roll back the booking so the user can retry
            $booking->cancel();
            session()->flash('error', $e->getMessage());
        }
    }

    // Reduces product line unit_amounts (from the end of the list) to absorb a
    // promo discount, since Thawani charges the sum of the lines rather than
    // accepting a single discount total.
    protected function applyDiscountToProductLines(array $products, int $discountBasisa): array
    {
        $remaining = $discountBasisa;

        for ($i = count($products) - 1; $i >= 0 && $remaining > 0; $i--) {
            $qty = $products[$i]['quantity'];
            $lineTotal = $products[$i]['unit_amount'] * $qty;
            $reduce = min($remaining, $lineTotal);

            $newLineTotal = $lineTotal - $reduce;
            $products[$i]['unit_amount'] = intdiv($newLineTotal, $qty);

            $remaining -= $lineTotal - ($products[$i]['unit_amount'] * $qty);
        }

        return $products;
    }

    // ── NBO redirect ─────────────────────────────────────────────────────────

    protected function redirectToNbo(Booking $booking)
    {
        try {
            $nbo = app(NboService::class);

            if (! $nbo->isConfigured()) {
                throw new Exception(__('Payment gateway is not configured. Please contact support.'));
            }

            $paymentUrl = $nbo->initiatePayment($booking);

            return redirect()->away($paymentUrl);

        } catch (Exception $e) {
            $booking->cancel();
            session()->flash('error', $e->getMessage());
        }
    }

    // ── CCAvenue redirect ────────────────────────────────────────────────────

    protected function redirectToCcavenue(Booking $booking)
    {
        try {
            $ccavenue = app(CCAvenueService::class);

            if (! $ccavenue->isConfigured()) {
                throw new Exception(__('Payment gateway is not configured. Please contact support.'));
            }

            return redirect()->route('payment.redirect.ccavenue', $booking->booking_reference);

        } catch (Exception $e) {
            $booking->cancel();
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $siteName = BookingSetting::get('site_name_'.app()->getLocale(), config('app.name', 'Bookings'));
        $pageTitle = $this->event->getTranslation('title', app()->getLocale()).' | '.$siteName;

        if ($this->passwordRequired) {
            return view('livewire.event-booking')->title($pageTitle);
        }

        // Only step 1's calendar view reads these — both walk every future
        // time slot on the event, so skip them on every other step's render
        // (ticket/extras/attendee/payment steps re-render on each wire:click).
        $availableDates = $this->step === 1 ? collect($this->event->getBookableDates()) : collect();
        $soldOutDates = $this->step === 1 ? collect($this->event->getSoldOutDates()) : collect();
        $timeSlots = $this->selectedDate
            ? $this->event->timeSlots()->where('is_active', true)->where('date', $this->selectedDate)->get()->filter(fn ($slot) => $this->isSlotBookable($slot))->values()
            : collect();

        return view('livewire.event-booking', [
            'availableDates' => $availableDates,
            'soldOutDates' => $soldOutDates,
            'timeSlots' => $timeSlots,
            'enforceCapacity' => $this->enforcesCapacity(),
            'kioskMode' => false,
            'ticketTypes' => $this->loadedTicketTypes(),
            'extraServices' => $this->loadedExtraServices(),
            'showEmail' => $this->showEmail,
            'showPhone' => $this->showPhone,
            'showDateOfBirth' => $this->showDateOfBirth,
            'showGender' => $this->showGender,
            'showNationality' => $this->showNationality,
            'showIdentityNumber' => $this->showIdentityNumber,
            'requireEmail' => $this->requireEmail,
            'requirePhone' => $this->requirePhone,
            'requireDateOfBirth' => $this->requireDateOfBirth,
            'requireGender' => $this->requireGender,
            'requireNationality' => $this->requireNationality,
            'requireIdentityNumber' => $this->requireIdentityNumber,
            'showPassportNumber' => $this->showPassportNumber,
            'requirePassportNumber' => $this->requirePassportNumber,
            'showIdentityCardUpload' => $this->showIdentityCardUpload,
            'requireIdentityCardUpload' => $this->requireIdentityCardUpload,
            'showPassportUpload' => $this->showPassportUpload,
            'requirePassportUpload' => $this->requirePassportUpload,
            'showSlotEndTime' => $this->showSlotEndTime,
            'minBirthDate' => $this->minBirthDate(),
            'maxBirthDate' => now()->format('Y-m-d'),
        ])->title($pageTitle);
    }
}

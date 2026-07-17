<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Livewire\Concerns\GuardsPrivateEvents;
use App\Livewire\Concerns\HandlesEventBookingFlow;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Services\ThawaniService;
use App\Services\NboService;
use App\Services\CCAvenueService;

class EventBooking extends Component
{
    use HandlesEventBookingFlow, GuardsPrivateEvents;

    // Payment
    public string $selectedPaymentMethod = '';
    public string $activeGateway         = 'free';

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

        $this->activeGateway           = (string) BookingSetting::get('active_gateway', 'free');
        $this->selectedPaymentMethod   = $this->activeGateway;
    }

    // Step 6 "Confirm / Pay" — create the booking and handle the active gateway.

    public function submitBooking()
    {
        try {
            $booking = $this->persistBooking(function (float $ticketPrice, float $servicesPrice, float $discountAmount) {
                $isFree = ($ticketPrice + $servicesPrice - $discountAmount) <= 0;

                return [
                    'source'         => 'online',
                    'payment_method' => $isFree ? 'free' : $this->activeGateway,
                ];
            });
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $isFree = $booking->payment_method === 'free';

        // ── Route by gateway ────────────────────────────────────────────
        if (!$isFree && $this->activeGateway === 'thawani') {
            return $this->redirectToThawani($booking);
        }

        if (!$isFree && $this->activeGateway === 'nbo') {
            return $this->redirectToNbo($booking);
        }

        if (!$isFree && $this->activeGateway === 'ccavenue') {
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

            if (!$thawani->isConfigured()) {
                throw new Exception(__('Payment gateway is not configured. Please contact support.'));
            }

            $ticketTypesById   = $this->loadedTicketTypes()->keyBy('id');
            $extraServicesById = $this->loadedExtraServices()->keyBy('id');

            // Build product line(s) — one line per distinct ticket type selected
            $products = [];
            foreach ($this->ticketQuantities as $typeId => $qty) {
                if ($qty <= 0) continue;
                $ticketType = $ticketTypesById[$typeId] ?? null;
                if (!$ticketType) continue;
                $products[] = [
                    'name'        => $ticketType->getTranslation('name', 'en'),
                    'quantity'    => $qty,
                    'unit_amount' => $thawani->toBasisa((float) $ticketType->price),
                ];
            }

            // Add extra-service lines
            foreach ($this->ticketTypeServices as $typeId => $serviceCounts) {
                $qty = $this->ticketQuantities[$typeId] ?? 0;
                if ($qty <= 0 || empty($serviceCounts)) continue;
                foreach ($serviceCounts as $serviceId => $count) {
                    if ($count <= 0) continue;
                    $service = $extraServicesById[$serviceId] ?? null;
                    if (!$service) continue;
                    $products[] = [
                        'name'        => $service->getTranslation('name', 'en'),
                        'quantity'    => $count,
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
                'products'            => $products,
                'success_url'         => route('payment.callback') . '?reference=' . $booking->booking_reference,
                'cancel_url'          => route('event.booking', $booking->event->slug),
                'metadata'            => ['booking_id' => $booking->id],
            ], $booking);

            $sessionId = $response['data']['session_id'] ?? null;

            if (!$sessionId) {
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
            $qty       = $products[$i]['quantity'];
            $lineTotal = $products[$i]['unit_amount'] * $qty;
            $reduce    = min($remaining, $lineTotal);

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

            if (!$nbo->isConfigured()) {
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

            if (!$ccavenue->isConfigured()) {
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
        if ($this->passwordRequired) {
            return view('livewire.event-booking');
        }

        $availableDates = collect($this->event->getBookableDates());
        $soldOutDates   = collect($this->event->getSoldOutDates());
        $timeSlots      = $this->selectedDate
            ? $this->event->timeSlots()->where('is_active', true)->where('date', $this->selectedDate)->get()->filter(fn ($slot) => $this->isSlotBookable($slot))->values()
            : collect();

        return view('livewire.event-booking', [
            'availableDates'  => $availableDates,
            'soldOutDates'    => $soldOutDates,
            'timeSlots'       => $timeSlots,
            'ticketTypes'     => $this->loadedTicketTypes(),
            'extraServices'   => $this->loadedExtraServices(),
            'showEmail'       => $this->showEmail,
            'showPhone'       => $this->showPhone,
            'showDateOfBirth' => $this->showDateOfBirth,
            'showGender'      => $this->showGender,
            'showNationality' => $this->showNationality,
            'showIdentityNumber' => $this->showIdentityNumber,
            'requireEmail'       => $this->requireEmail,
            'requirePhone'       => $this->requirePhone,
            'requireDateOfBirth' => $this->requireDateOfBirth,
            'requireGender'      => $this->requireGender,
            'requireNationality' => $this->requireNationality,
            'requireIdentityNumber' => $this->requireIdentityNumber,
            'showSlotEndTime' => $this->showSlotEndTime,
            'minBirthDate'    => $this->minBirthDate(),
            'maxBirthDate'    => now()->format('Y-m-d'),
        ]);
    }
}

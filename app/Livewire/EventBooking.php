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
            $booking = $this->persistBooking(function (float $ticketPrice, float $servicesPrice) {
                $isFree = ($ticketPrice + $servicesPrice) <= 0;

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
            'minBirthDate'    => $this->minBirthDate(),
            'maxBirthDate'    => now()->format('Y-m-d'),
        ]);
    }
}

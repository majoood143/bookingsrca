<?php

namespace App\Livewire\Kiosk;

use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Concerns\HandlesEventBookingFlow;
use App\Models\Event;
use App\Models\Kiosk;
use App\Models\KioskCard;
use App\Models\Booking;

class KioskBooking extends Component
{
    use HandlesEventBookingFlow;

    public Kiosk $kiosk;

    public ?Booking $confirmedBooking = null;

    public bool $awaitingCardTap = false;

    // Set in mount() when the kiosk isn't pinned to one event — step 0 shows
    // an event picker before entering the normal date/slot/tickets flow.
    public bool $eventLocked = true;

    public function mount(Kiosk $kiosk)
    {
        $this->kiosk = $kiosk;
        $this->loadBookingFieldSettings();

        $this->eventLocked = $kiosk->event_id !== null;

        if ($this->eventLocked) {
            $this->event = $kiosk->event;
            $this->step = 1;
        } else {
            $this->step = 0;
        }
    }

    // ── Step 0 — event picker (only shown when the kiosk isn't pinned) ───────

    public function chooseEvent($eventId)
    {
        $event = Event::published()->upcoming()->findOrFail($eventId);

        $this->event = $event;
        $this->step = 1;
    }

    // ── Idle reset — called by the front-end idle timer via wire:click/poll ──

    public function resetKiosk(): void
    {
        $this->selectedDate = null;
        $this->selectedSlot = null;
        $this->ticketQuantities = [];
        $this->ticketTypeRemaining = [];
        $this->slotRemainingCapacity = 0;
        $this->ticketTypeServices = [];
        $this->attendees = [];
        $this->copyContactToAll = false;
        $this->agreedToTerms = false;
        $this->awaitingCardTap = false;
        $this->confirmedBooking = null;
        $this->totalPrice = 0;

        if (!$this->eventLocked) {
            unset($this->event);
        }

        $this->step = $this->eventLocked ? 1 : 0;
    }

    // ── Step 6 — payment method choice ────────────────────────────────────

    public function selectWalletPayment(): void
    {
        $this->awaitingCardTap = true;
    }

    public function cancelWalletPayment(): void
    {
        $this->awaitingCardTap = false;
    }

    // Called by the kiosk app's JS bridge when the ACR122U reads a card UID.
    #[On('card-tapped')]
    public function onCardTapped($uid): void
    {
        if (!$this->awaitingCardTap) {
            return;
        }

        $this->awaitingCardTap = false;

        try {
            $booking = $this->persistBooking(
                function (float $ticketPrice, float $servicesPrice) {
                    $isFree = ($ticketPrice + $servicesPrice) <= 0;

                    return [
                        'source'         => 'kiosk',
                        'kiosk_id'       => $this->kiosk->id,
                        'payment_method' => $isFree ? 'free' : 'kiosk_wallet',
                    ];
                },
                function (Booking $booking, float $ticketPrice, float $servicesPrice) use ($uid) {
                    $total = $ticketPrice + $servicesPrice;

                    if ($total <= 0) {
                        return;
                    }

                    $card = KioskCard::where('uid', $uid)->lockForUpdate()->first();

                    if (!$card) {
                        throw new Exception(__('kiosk_booking.wallet.card_not_found'));
                    }

                    if (!$card->isActive()) {
                        throw new Exception(__('kiosk_booking.wallet.card_blocked'));
                    }

                    if (!$card->hasSufficientBalance($total)) {
                        throw new Exception(__('kiosk_booking.wallet.insufficient_balance'));
                    }

                    $card->applyTransaction('payment', -$total, [
                        'kiosk_id'   => $this->kiosk->id,
                        'booking_id' => $booking->id,
                    ]);

                    $booking->update(['payment_status' => 'paid']);
                }
            );
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $booking->confirm();
        $this->showConfirmation($booking);
    }

    public function payAtCounter(): void
    {
        try {
            $booking = $this->persistBooking(function (float $ticketPrice, float $servicesPrice) {
                $isFree = ($ticketPrice + $servicesPrice) <= 0;

                return [
                    'source'         => 'kiosk',
                    'kiosk_id'       => $this->kiosk->id,
                    'payment_method' => $isFree ? 'free' : 'cash',
                ];
            });
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        // Nothing to collect — confirm immediately, same as the free path everywhere else.
        if ($booking->payment_method === 'free') {
            $booking->update(['payment_status' => 'paid']);
            $booking->confirm();
        }

        // Otherwise stays pending: staff records the payment at the counter via
        // the existing BookingPayment ledger, then confirms it from BookingResource.
        $this->showConfirmation($booking);
    }

    // submitBooking() is required by HandlesEventBookingFlow::goToPaymentStep()
    // as the zero-total fast path — kiosk treats that exactly like counter payment
    // (nothing to charge either way).
    public function submitBooking(): void
    {
        $this->payAtCounter();
    }

    protected function showConfirmation(Booking $booking): void
    {
        $this->confirmedBooking = $booking->load(['event', 'timeSlot', 'attendees', 'extraServices']);
        $this->step = 7;

        $this->dispatch('print-receipt', receipt: [
            'booking_reference' => $booking->booking_reference,
            'event_title'       => $this->event->getTranslation('title', app()->getLocale()),
            'quantity'          => $booking->quantity,
            'total_price'       => (float) $booking->total_price,
            'payment_method'    => $booking->payment_method,
            'payment_status'    => $booking->payment_status,
            'footer'            => $this->kiosk->getTranslation('receipt_footer_text', app()->getLocale()),
        ]);
    }

    public function render()
    {
        $availableDates = $this->step >= 1 && isset($this->event)
            ? collect($this->event->getBookableDates())
            : collect();
        $soldOutDates = $this->step >= 1 && isset($this->event)
            ? collect($this->event->getSoldOutDates())
            : collect();
        $timeSlots = ($this->selectedDate && isset($this->event))
            ? $this->event->timeSlots()->where('is_active', true)->where('date', $this->selectedDate)->get()->filter(fn ($slot) => $this->isSlotBookable($slot))->values()
            : collect();

        return view('livewire.kiosk.kiosk-booking', [
            'pickableEvents'  => $this->eventLocked ? collect() : Event::published()->upcoming()->orderBy('start_date')->get(),
            'availableDates'  => $availableDates,
            'soldOutDates'    => $soldOutDates,
            'timeSlots'       => $timeSlots,
            'ticketTypes'     => isset($this->event) ? $this->loadedTicketTypes() : collect(),
            'extraServices'   => isset($this->event) ? $this->loadedExtraServices() : collect(),
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

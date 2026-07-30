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

    // Kiosk never shows the date-picker calendar the public site uses — it's
    // locked to "today" and jumps straight to slot selection. This is only
    // true when today has no bookable/unsold-out slots at all, so the view
    // can show a dead-end message instead of a date grid.
    public bool $noAvailabilityToday = false;

    // Gates the welcome screen (two giant EN/AR "start" buttons) in front of
    // the wizard. Stays false until the customer taps one, and flips back to
    // false on resetKiosk() so the idle timer returns here between guests.
    public bool $started = false;

    // ── Progressive one-at-a-time attendee flow (kiosk-only, see the
    // attendee-flow-progressive partial) ──────────────────────────────────
    //
    // $focusedAttendee is the array index of the attendee currently being
    // filled in. Once it reaches count($attendees) every attendee has been
    // confirmed (or copied via applySameDetailsToAll()) and the terms/submit
    // screen shows. $applySameToAll is null while attendee 0's "use my
    // details for everyone?" decision hasn't been made yet.
    public int $focusedAttendee = 0;
    public ?bool $applySameToAll = null;

    public function mount(Kiosk $kiosk)
    {
        $this->kiosk = $kiosk;
        $this->eventLocked = $kiosk->event_id !== null;

        if ($this->eventLocked) {
            $this->event = $kiosk->event;
            $this->lockToToday();
        } else {
            $this->step = 0;
        }

        // Runs after $this->event is set (when pinned) so event-level terms
        // & conditions overrides are picked up from the start.
        $this->loadBookingFieldSettings();
    }

    // ── Step 0 — event picker (only shown when the kiosk isn't pinned) ───────

    public function chooseEvent($eventId)
    {
        $event = Event::published()->upcoming()->findOrFail($eventId);

        $this->event = $event;
        $this->lockToToday();

        // Re-resolve terms & conditions now that the event is known.
        $this->loadBookingFieldSettings();
    }

    // Skips the shared wizard's date-picker step (step 1) for the kiosk: a
    // walk-in customer only ever books "now", so we auto-select today's date
    // and land straight on slot selection (step 2). isSlotBookable() already
    // filters out any slot whose start time has passed, so whatever's left
    // is exactly what's still bookable for the rest of today.
    protected function lockToToday(): void
    {
        $today = now()->format('Y-m-d');
        $bookable = $this->event->getBookableDates();

        // A "sold out" today doesn't turn the kiosk away — see enforcesCapacity().
        // Only a genuine absence of any active/future slot today is a dead end.
        if (in_array($today, $bookable)) {
            $this->noAvailabilityToday = false;
            $this->selectDate($today);
        } else {
            $this->noAvailabilityToday = true;
            $this->selectedDate = null;
            $this->step = 1;
        }
    }

    // Walk-in kiosk guests are never blocked by remaining stock — a member of
    // staff resolves any resulting overbooking from the admin panel afterwards.
    protected function enforcesCapacity(): bool
    {
        return false;
    }

    // ── Welcome screen — two giant EN/AR buttons gate the wizard so the kiosk
    // sits idle on a clear "tap to begin" screen between guests ────────────

    public function startBooking(string $locale): void
    {
        if (in_array($locale, ['en', 'ar'], true)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        $this->started = true;
        $this->dispatch('kiosk-started');
    }

    // ── Idle reset — called by the front-end idle timer via wire:click/poll ──

    public function resetKiosk(): void
    {
        $this->started = false;
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
        $this->focusedAttendee = 0;
        $this->applySameToAll = null;

        if ($this->eventLocked) {
            // Re-run the today-lock: a new day may have started, or a slot
            // that was full a minute ago may have freed up.
            $this->lockToToday();
        } else {
            unset($this->event);
            $this->noAvailabilityToday = false;
            $this->step = 0;
        }

        $this->dispatch('kiosk-reset');
    }

    // ── Progressive attendee flow ───────────────────────────────────────────

    // Livewire lifecycle hook — fires whenever $step changes for any reason
    // (nextStep(), previousStep(), etc.). Restarts the guided attendee flow
    // fresh each time step 5 is (re)entered, since buildAttendees() may have
    // reshuffled the attendee list if the customer went back and changed
    // ticket quantities.
    public function updatedStep($value): void
    {
        if ((int) $value === 5) {
            $this->focusedAttendee = 0;
            $this->applySameToAll = null;
        }

        // Every forward/back navigation (nextStep, previousStep, selectDate,
        // selectSlot, chooseEvent...) lands here, so this is the one place to
        // animate every step transition rather than special-casing each caller.
        $this->dispatch('kiosk-step-changed');
    }

    // Validates just this one attendee's fields (progressive disclosure — the
    // customer is never blocked by errors on attendees they haven't reached
    // yet) and advances focus to the next one.
    public function confirmAttendee(int $index): void
    {
        $this->validateAttendeeFields($index);
        $this->focusedAttendee = $index + 1;

        // Re-open the "use these details for everyone?" decision whenever
        // attendee 0 is (re)confirmed — their details are what would be copied.
        if ($index === 0) {
            $this->applySameToAll = null;
        }

        $this->dispatch('attendee-focus-changed');
    }

    // Attendee 0 said "yes, use my details for everyone" — copy every field
    // except the ticket type (stays per-slot) and document uploads (a photo
    // ID is uniquely personal; each attendee still supplies their own if the
    // event requires one — final validation on submit catches that).
    public function applySameDetailsToAll(): void
    {
        $this->validateAttendeeFields(0);

        $skip = ['ticket_type_id', 'identity_card_upload', 'passport_upload'];
        $source = $this->attendees[0];

        foreach ($this->attendees as $i => &$attendee) {
            if ($i === 0) continue;
            foreach ($source as $field => $value) {
                if (in_array($field, $skip, true)) continue;
                $attendee[$field] = $value;
            }
        }
        unset($attendee);

        $this->applySameToAll = true;
        $this->focusedAttendee = count($this->attendees);
        $this->dispatch('attendee-focus-changed');
    }

    // Attendee 0 said "no" — walk through attendees 1..N individually.
    public function declineApplySameToAll(): void
    {
        $this->applySameToAll = false;
        $this->focusedAttendee = max(1, $this->focusedAttendee);
        $this->dispatch('attendee-focus-changed');
    }

    // Revisit an already-confirmed attendee from the tracker/summary. Editing
    // attendee 0 reopens the apply-to-all decision; editing anyone else just
    // switches into (or stays in) individual editing mode for that person.
    public function editAttendee(int $index): void
    {
        if ($index > $this->focusedAttendee) {
            return;
        }

        $this->applySameToAll = $index === 0 ? null : false;
        $this->focusedAttendee = $index;
        $this->dispatch('attendee-focus-changed');
    }

    protected function validateAttendeeFields(int $index): void
    {
        $rules = collect($this->rules())
            ->filter(fn ($rule, $key) => str_starts_with($key, "attendees.$index."))
            ->all();

        $this->validate($rules);
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

    // Kiosk gets its own field-visibility override scope, independent from
    // the public event booking page.
    protected function fieldVisibilityScope(): string
    {
        return 'kiosk';
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
            'enforceCapacity' => $this->enforcesCapacity(),
            'progressiveAttendees' => true,
            'kioskMode'       => true,
            'ticketTypes'     => isset($this->event) ? $this->loadedTicketTypes() : collect(),
            'extraServices'   => isset($this->event) ? $this->loadedExtraServices() : collect(),
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
            'showPassportNumber'       => $this->showPassportNumber,
            'requirePassportNumber'    => $this->requirePassportNumber,
            'showIdentityCardUpload'   => $this->showIdentityCardUpload,
            'requireIdentityCardUpload' => $this->requireIdentityCardUpload,
            'showPassportUpload'       => $this->showPassportUpload,
            'requirePassportUpload'    => $this->requirePassportUpload,
            'showSlotEndTime' => $this->showSlotEndTime,
            'minBirthDate'    => $this->minBirthDate(),
            'maxBirthDate'    => now()->format('Y-m-d'),
        ]);
    }
}

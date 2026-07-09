<?php

namespace App\Livewire\Concerns;

use Exception;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TicketType;
use App\Models\ExtraService;
use App\Models\BookingAttendee;
use App\Models\Booking;
use App\Models\BookingSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Domain logic shared by every booking wizard screen (public web + kiosk):
// ticket/service quantity math, dependency validation, attendee building,
// and the stock-locked booking + attendee + extra-service creation that
// belongs inside one DB transaction. Payment routing (which gateway, or
// which kiosk hardware) stays in each consuming component.
trait HandlesEventBookingFlow
{
    // Nullable so a kiosk not pinned to one event can leave this unset until
    // the customer picks one (step 0) — Livewire would error dehydrating an
    // uninitialized typed property, so `null` is the safe default everywhere.
    // The web flow always receives it via route-model binding before mount().
    public ?Event $event = null;
    public $selectedDate = null;
    public $selectedSlot = null;

    /** @var array [ticket_type_id => qty] */
    public $ticketQuantities = [];

    /** @var array [ticket_type_id => remaining stock], snapshotted when the slot is selected */
    public $ticketTypeRemaining = [];

    /** @var int remaining capacity of the selected time slot, snapshotted when the slot is selected */
    public $slotRemainingCapacity = 0;

    /** @var array [ticket_type_id => [service_id => count_of_that_type's_tickets_with_the_service]] */
    public $ticketTypeServices = [];

    /** @var array of attendee data arrays (one per ticket across all types) */
    public $attendees = [];

    public bool $copyContactToAll = false;

    public $maxTickets;
    public $minTickets;

    // Field visibility from settings
    public bool $showEmail       = true;
    public bool $showPhone       = true;
    public bool $showDateOfBirth = true;
    public bool $showGender      = true;
    public bool $showNationality = true;
    public bool $showIdentityNumber = true;
    public int  $maxAttendeeAge  = 75;

    // Terms and conditions
    public string $termsEn       = '';
    public string $termsAr       = '';
    public bool   $agreedToTerms = false;

    public $step       = 1;
    public $totalPrice = 0;

    // Per-request in-memory cache — not serialized by Livewire, so these are
    // refreshed each Livewire request but shared across all methods within it.
    private ?Collection $cachedTicketTypes   = null;
    private ?Collection $cachedExtraServices = null;

    protected function loadedTicketTypes(): Collection
    {
        return $this->cachedTicketTypes ??= $this->event->ticketTypes()->with('dependsOnMany')->where('is_active', true)->get();
    }

    protected function loadedExtraServices(): Collection
    {
        return $this->cachedExtraServices ??= $this->event->extraServices()->where('is_active', true)->get();
    }

    public function rules(): array
    {
        $rules = [
            'selectedDate' => 'required',
            'selectedSlot' => 'required|exists:time_slots,id',
            'agreedToTerms' => ($this->termsEn || $this->termsAr) ? 'accepted' : 'nullable',
        ];

        foreach ($this->attendees as $i => $attendee) {
            $rules["attendees.$i.first_name"]    = 'required|string|max:255';
            $rules["attendees.$i.last_name"]     = 'required|string|max:255';
            $rules["attendees.$i.email"]         = $this->showEmail       ? 'required|email|max:255'  : 'nullable';
            $rules["attendees.$i.phone"]         = $this->showPhone       ? ['required', 'string', 'regex:/^\+?\d{7,15}$/'] : 'nullable';
            $rules["attendees.$i.date_of_birth"] = $this->showDateOfBirth
                ? "nullable|date|before_or_equal:today|after_or_equal:{$this->minBirthDate()}"
                : 'nullable';
            $rules["attendees.$i.gender"]        = $this->showGender      ? 'required|in:male,female' : 'nullable';
            $rules["attendees.$i.nationality"]   = $this->showNationality ? 'nullable|string|max:100' : 'nullable';
            $rules["attendees.$i.identity_number"] = $this->showIdentityNumber ? 'required|string|max:50' : 'nullable';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'attendees.*.date_of_birth.date'            => __('event_booking.step5.date_of_birth_invalid'),
            'attendees.*.date_of_birth.after_or_equal'  => __('event_booking.step5.date_of_birth_max_age', ['age' => $this->maxAttendeeAge]),
            'attendees.*.date_of_birth.before_or_equal' => __('event_booking.step5.date_of_birth_future'),
            'attendees.*.phone.regex'                   => __('event_booking.step5.phone_invalid'),
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'selectedDate'                => __('event_booking.step1.heading'),
            'selectedSlot'                => __('event_booking.step2.heading'),
            'agreedToTerms'                => __('event_booking.step5.terms_heading'),
            'attendees.*.first_name'      => __('event_booking.step5.first_name'),
            'attendees.*.last_name'       => __('event_booking.step5.last_name'),
            'attendees.*.email'          => __('event_booking.step5.email_address'),
            'attendees.*.phone'          => __('event_booking.step5.phone_number'),
            'attendees.*.date_of_birth'  => __('event_booking.step5.date_of_birth'),
            'attendees.*.gender'          => __('event_booking.step5.gender'),
            'attendees.*.nationality'    => __('event_booking.step5.nationality'),
            'attendees.*.identity_number' => __('event_booking.step5.identity_number'),
        ];
    }

    protected function minBirthDate(): string
    {
        return now()->subYears($this->maxAttendeeAge)->format('Y-m-d');
    }

    // Loads the global field-visibility/terms/ticket-limit settings shared by
    // every booking screen. Call from mount(); payment settings are loaded
    // separately by each component since they differ per screen.
    protected function loadBookingFieldSettings(): void
    {
        $this->maxTickets = BookingSetting::get('max_tickets_per_booking', 10);
        $this->minTickets = BookingSetting::get('min_tickets_per_booking', 1);

        $this->showEmail       = (bool) BookingSetting::get('show_email', true);
        $this->showPhone       = (bool) BookingSetting::get('show_phone', true);
        $this->showDateOfBirth = (bool) BookingSetting::get('show_date_of_birth', true);
        $this->showGender      = (bool) BookingSetting::get('show_gender', true);
        $this->showNationality = (bool) BookingSetting::get('show_nationality', true);
        $this->showIdentityNumber = (bool) BookingSetting::get('show_identity_number', true);
        $this->maxAttendeeAge  = (int) BookingSetting::get('max_attendee_age_years', 75);

        $this->termsEn = (string) BookingSetting::get('terms_en', '');
        $this->termsAr = (string) BookingSetting::get('terms_ar', '');
    }

    public function selectDate($date)
    {
        if (!in_array($date, $this->event->getBookableDates())) {
            session()->flash('error', __('That date is no longer available. Please choose another date.'));
            return;
        }

        if (in_array($date, $this->event->getSoldOutDates())) {
            session()->flash('error', __('event_booking.step1.sold_out_error'));
            return;
        }

        $this->selectedDate = $date;
        $this->selectedSlot = null;
        $this->step = 2;
    }

    public function selectSlot($slotId)
    {
        $slot = TimeSlot::where('event_id', $this->event->id)
            ->where('date', $this->selectedDate)
            ->find($slotId);

        if (!$slot || !$this->isSlotBookable($slot)) {
            session()->flash('error', __('That time slot is no longer available. Please choose another.'));
            return;
        }

        $this->selectedSlot = $slotId;
        $this->slotRemainingCapacity = $slot->getRemainingCapacity();

        // Initialise quantities to 0 for each active ticket type, and snapshot
        // each type's remaining stock at the moment the customer starts picking
        // tickets, so the stepper can be capped without re-querying on every click.
        foreach ($this->loadedTicketTypes() as $type) {
            if (!array_key_exists($type->id, $this->ticketQuantities)) {
                $this->ticketQuantities[$type->id] = 0;
            }
            $this->ticketTypeRemaining[$type->id] = $type->getRemainingQuantity();
        }

        $this->step = 3;
    }

    // A slot is bookable if it's active and its start datetime hasn't passed.
    protected function isSlotBookable(TimeSlot $slot): bool
    {
        if (!$slot->is_active) {
            return false;
        }

        $slotStart = \Carbon\Carbon::parse(
            $slot->date->format('Y-m-d') . ' ' . $slot->start_time->format('H:i:s')
        );

        return $slotStart->isFuture();
    }

    // ── Quantity helpers ────────────────────────────────────────────────────

    public function incrementQuantity($typeId)
    {
        $current   = $this->ticketQuantities[$typeId] ?? 0;
        $total     = array_sum($this->ticketQuantities);
        $remaining = $this->ticketTypeRemaining[$typeId] ?? 0;

        if ($current < $this->maxTickets
            && $total < $this->maxTickets
            && $current < $remaining
            && $total < $this->slotRemainingCapacity
        ) {
            $this->ticketQuantities[$typeId] = $current + 1;
            $this->calculateTotal();
        }
    }

    public function decrementQuantity($typeId)
    {
        $current = $this->ticketQuantities[$typeId] ?? 0;
        if ($current > 0) {
            $this->ticketQuantities[$typeId] = $current - 1;
            $this->clampServiceQuantities($typeId);

            // When a dependency drops to 0, zero out dependent ticket types that no
            // longer have any of their required dependencies selected (OR logic).
            if ($this->ticketQuantities[$typeId] === 0) {
                foreach ($this->loadedTicketTypes() as $type) {
                    $dependencyIds = $type->dependsOnMany->pluck('id')->all();

                    if (!in_array((int) $typeId, $dependencyIds)
                        || ($this->ticketQuantities[$type->id] ?? 0) <= 0
                    ) {
                        continue;
                    }

                    $stillSatisfied = collect($dependencyIds)
                        ->contains(fn ($id) => ($this->ticketQuantities[$id] ?? 0) > 0);

                    if (!$stillSatisfied) {
                        $this->ticketQuantities[$type->id] = 0;
                        $this->clampServiceQuantities($type->id);
                    }
                }
            }

            $this->calculateTotal();
        }
    }

    public function getTotalQuantity(): int
    {
        return (int) array_sum($this->ticketQuantities);
    }

    // ── Service quantity (per ticket type, how many of that type's tickets include each service) ──

    public function incrementServiceQty($typeId, $serviceId)
    {
        $typeQty = $this->ticketQuantities[$typeId] ?? 0;
        $current = $this->ticketTypeServices[$typeId][$serviceId] ?? 0;

        if ($current < $typeQty) {
            $this->ticketTypeServices[$typeId][$serviceId] = $current + 1;
            $this->calculateTotal();
        }
    }

    public function decrementServiceQty($typeId, $serviceId)
    {
        $current = $this->ticketTypeServices[$typeId][$serviceId] ?? 0;

        if ($current > 0) {
            $this->ticketTypeServices[$typeId][$serviceId] = $current - 1;
            $this->calculateTotal();
        }
    }

    // Caps each service's selected count for a ticket type to its new (lower) quantity.
    protected function clampServiceQuantities($typeId): void
    {
        $qty = $this->ticketQuantities[$typeId] ?? 0;

        foreach ($this->ticketTypeServices[$typeId] ?? [] as $serviceId => $count) {
            if ($count > $qty) {
                $this->ticketTypeServices[$typeId][$serviceId] = $qty;
            }
        }
    }

    // ── Price calculation ───────────────────────────────────────────────────

    public function calculateTotal()
    {
        $this->totalPrice = 0;

        $ticketTypes   = $this->loadedTicketTypes()->keyBy('id');
        $extraServices = $this->loadedExtraServices()->keyBy('id');

        foreach ($this->ticketQuantities as $typeId => $qty) {
            if ($qty <= 0) continue;

            $ticketType = $ticketTypes[$typeId] ?? null;
            if ($ticketType) {
                $this->totalPrice += $ticketType->price * $qty;
            }

            foreach ($this->ticketTypeServices[$typeId] ?? [] as $serviceId => $count) {
                if ($count > 0 && isset($extraServices[$serviceId])) {
                    $this->totalPrice += $extraServices[$serviceId]->price * $count;
                }
            }
        }
    }

    // ── Attendee helpers ────────────────────────────────────────────────────

    protected function buildAttendees(): void
    {
        $newAttendees = [];

        foreach ($this->ticketQuantities as $typeId => $qty) {
            for ($i = 0; $i < $qty; $i++) {
                $newAttendees[] = [
                    'ticket_type_id' => $typeId,
                    'first_name'     => '',
                    'last_name'      => '',
                    'email'          => '',
                    'phone'          => '',
                    'date_of_birth'  => null,
                    'gender'         => null,
                    'nationality'    => '',
                    'identity_number' => '',
                ];
            }
        }

        // Preserve data already entered for existing indices
        foreach ($newAttendees as $i => &$attendee) {
            if (isset($this->attendees[$i])) {
                $attendee = array_merge($attendee, array_intersect_key($this->attendees[$i], $attendee));
            }
        }

        $this->attendees = $newAttendees;
    }

    // When the checkbox is toggled, copy email + phone from attendee 0 to the rest
    public function updatedCopyContactToAll(bool $value): void
    {
        if ($value && !empty($this->attendees)) {
            $email = $this->attendees[0]['email'] ?? '';
            $phone = $this->attendees[0]['phone'] ?? '';

            foreach ($this->attendees as $i => &$attendee) {
                if ($i > 0) {
                    $attendee['email'] = $email;
                    $attendee['phone'] = $phone;
                }
            }
        }
    }

    // Propagate first-attendee email/phone changes when the copy option is on
    public function updatedAttendees($value, $key): void
    {
        if (!$this->copyContactToAll) return;

        $parts = explode('.', $key);
        if (($parts[0] ?? '') !== '0') return;
        if (!in_array($parts[1] ?? '', ['email', 'phone'])) return;

        $field = $parts[1];
        $newValue = $this->attendees[0][$field] ?? '';

        foreach ($this->attendees as $i => &$attendee) {
            if ($i > 0) {
                $attendee[$field] = $newValue;
            }
        }
    }

    // ── Navigation ──────────────────────────────────────────────────────────

    public function nextStep(): void
    {
        if ($this->step === 3) {
            if ($this->getTotalQuantity() < $this->minTickets) {
                $this->addError('ticketQuantities', __('Please select at least :n ticket(s).', ['n' => $this->minTickets]));
                return;
            }

            // Enforce ticket dependencies — at least one of the required
            // dependency ticket types must be selected (OR logic).
            foreach ($this->loadedTicketTypes() as $type) {
                $dependencyIds = $type->dependsOnMany->pluck('id')->all();

                if (empty($dependencyIds) || ($this->ticketQuantities[$type->id] ?? 0) <= 0) {
                    continue;
                }

                $satisfied = collect($dependencyIds)
                    ->contains(fn ($id) => ($this->ticketQuantities[$id] ?? 0) > 0);

                if (!$satisfied) {
                    $parentNames = $this->loadedTicketTypes()
                        ->whereIn('id', $dependencyIds)
                        ->map(fn ($t) => $t->getTranslation('name', app()->getLocale()))
                        ->implode(', ');

                    $this->addError('ticketQuantities', __('event_booking.step3.dependency_required', [
                        'child'  => $type->getTranslation('name', app()->getLocale()),
                        'parent' => $parentNames,
                    ]));
                    return;
                }
            }

            $this->buildAttendees();
            $this->step = $this->hasExtraServices() ? 4 : 5;
            return;
        }

        $this->step++;
    }

    public function previousStep(): void
    {
        if ($this->step === 5 && !$this->hasExtraServices()) {
            $this->step = 3;
            return;
        }

        if ($this->step > 1) {
            $this->step--;
        }
    }

    // Whether the event has any active extra services — step 4 is skipped when it doesn't.
    protected function hasExtraServices(): bool
    {
        return $this->loadedExtraServices()->isNotEmpty();
    }

    // Step 5 "Continue to Payment" — validate attendees and move to the payment step,
    // or confirm immediately when nothing is payable (e.g. only free ticket types).
    public function goToPaymentStep()
    {
        $this->validate();

        if ($this->totalPrice <= 0) {
            return $this->submitBooking();
        }

        $this->step = 6;
    }

    // ── Booking persistence ─────────────────────────────────────────────────
    // Locks stock, creates the Booking + BookingAttendees + extra-service
    // pivots inside one DB transaction, and returns the created Booking.
    //
    // $resolveAttributes(float $ticketPrice, float $servicesPrice): array
    //     Called with the freshly recomputed (DB-locked) prices — returns the
    //     Booking attributes specific to the caller (source, payment_method, kiosk_id...).
    // $afterCreate(Booking $booking, float $ticketPrice, float $servicesPrice): void
    //     Runs inside the same transaction after the booking is persisted —
    //     e.g. deducting a kiosk wallet card's balance. Throwing here rolls
    //     back the whole booking, so a failed payment never leaves a stray row.
    protected function persistBooking(callable $resolveAttributes, ?callable $afterCreate = null): Booking
    {
        $totalQty = $this->getTotalQuantity();

        if ($totalQty === 0) {
            throw new Exception(__('Please select at least one ticket.'));
        }

        if (!in_array($this->selectedDate, $this->event->getBookableDates())) {
            throw new Exception(__('The selected date is no longer available. Please start over.'));
        }

        return DB::transaction(function () use ($totalQty, $resolveAttributes, $afterCreate) {
            $timeSlot = TimeSlot::where('event_id', $this->event->id)
                ->where('date', $this->selectedDate)
                ->lockForUpdate()
                ->find($this->selectedSlot);

            if (!$timeSlot || !$this->isSlotBookable($timeSlot)) {
                throw new Exception(__('Time slot is no longer available.'));
            }

            if (!$timeSlot->isAvailable($totalQty)) {
                throw new Exception(__('Time slot is not available for the requested quantity.'));
            }

            $ticketPrice   = 0;
            $servicesPrice = 0;

            foreach ($this->ticketQuantities as $typeId => $qty) {
                if ($qty <= 0) continue;

                $ticketType = TicketType::where('id', $typeId)->lockForUpdate()->first();
                if (!$ticketType->isAvailable($qty)) {
                    throw new Exception(__('Ticket type :name is not available.', [
                        'name' => $ticketType->getTranslation('name', app()->getLocale()),
                    ]));
                }

                $ticketPrice += $ticketType->price * $qty;

                if (!empty($this->ticketTypeServices[$typeId])) {
                    $services = ExtraService::whereIn('id', array_keys($this->ticketTypeServices[$typeId]))->lockForUpdate()->get()->keyBy('id');
                    foreach ($this->ticketTypeServices[$typeId] as $serviceId => $count) {
                        if ($count <= 0) continue;

                        $service = $services[$serviceId] ?? null;
                        if (!$service || !$service->isAvailable($count)) {
                            throw new Exception(__('Service :name is not available.', [
                                'name' => $service?->getTranslation('name', app()->getLocale()) ?? '',
                            ]));
                        }
                        $servicesPrice += $service->price * $count;
                    }
                }
            }

            // Primary ticket type = first selected type (for backward-compat column)
            $primaryTypeId = collect($this->ticketQuantities)
                ->filter(fn($q) => $q > 0)
                ->keys()
                ->first();

            $booking = Booking::create(array_merge([
                'event_id'        => $this->event->id,
                'time_slot_id'    => $this->selectedSlot,
                'ticket_type_id'  => $primaryTypeId,
                'event_date'      => $this->selectedDate,
                'quantity'        => $totalQty,
                'ticket_price'    => $ticketPrice,
                'services_price'  => $servicesPrice,
                'total_price'     => $ticketPrice + $servicesPrice,
                'locale'          => app()->getLocale(),
                'status'          => 'pending',
                'payment_status'  => 'pending',
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
            ], $resolveAttributes($ticketPrice, $servicesPrice)));

            // Create one BookingAttendee per ticket
            $ticketTypesById = $this->loadedTicketTypes()->keyBy('id');
            foreach ($this->attendees as $attendeeData) {
                $ticketType = $ticketTypesById[$attendeeData['ticket_type_id']] ?? null;

                BookingAttendee::create([
                    'booking_id'     => $booking->id,
                    'ticket_type_id' => $attendeeData['ticket_type_id'],
                    'ticket_price'   => $ticketType?->price ?? 0,
                    'first_name'     => $attendeeData['first_name'],
                    'last_name'      => $attendeeData['last_name'],
                    'email'          => $this->showEmail       ? ($attendeeData['email'] ?? '')         : '',
                    'phone'          => $this->showPhone       ? ($attendeeData['phone'] ?? null)        : null,
                    'date_of_birth'  => $this->showDateOfBirth ? ($attendeeData['date_of_birth'] ?? null) : null,
                    'gender'         => $this->showGender      ? ($attendeeData['gender'] ?? null)       : null,
                    'nationality'    => $this->showNationality ? ($attendeeData['nationality'] ?? null)  : null,
                    'identity_number' => $this->showIdentityNumber ? ($attendeeData['identity_number'] ?? null) : null,
                ]);
            }

            // Aggregate and attach extra services
            $extraServicesById = $this->loadedExtraServices()->keyBy('id');
            $aggregated = [];
            foreach ($this->ticketTypeServices as $typeId => $serviceCounts) {
                $qty = $this->ticketQuantities[$typeId] ?? 0;
                if ($qty <= 0 || empty($serviceCounts)) continue;

                foreach ($serviceCounts as $serviceId => $count) {
                    if ($count <= 0) continue;

                    if (isset($aggregated[$serviceId])) {
                        $aggregated[$serviceId]['quantity'] += $count;
                    } else {
                        $service = $extraServicesById[$serviceId] ?? null;
                        $aggregated[$serviceId] = ['quantity' => $count, 'price' => $service?->price ?? 0];
                    }
                }
            }
            foreach ($aggregated as $serviceId => $pivot) {
                $booking->extraServices()->attach($serviceId, $pivot);
            }

            if ($afterCreate) {
                $afterCreate($booking, $ticketPrice, $servicesPrice);
            }

            return $booking;
        });
    }
}

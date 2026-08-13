<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Marcelorodrigo\FilamentBarcodeScannerField\Forms\Components\BarcodeInput;

class AttendeeCheckIn extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static string|\UnitEnum|null $navigationGroup = 'Booking Management';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.attendee-check-in';

    public ?array $data = ['code' => null];

    public ?Booking $booking = null;

    /** @var Collection<int, Booking>|null */
    public ?Collection $candidateBookings = null;

    /**
     * Live check-in progress for the event/date of the most recently loaded
     * booking. Kept populated across "Scan Another" so staff see a running
     * tally while working through a queue of attendees for the same event.
     *
     * @var array{event_id: int, event_date: string, event_title: array<string, string>, total: int, checked_in: int}|null
     */
    public ?array $eventStats = null;

    public function mount(): void
    {
        $this->form->fill(['code' => null]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                BarcodeInput::make('code')
                    ->label(__('attendee_check_in.search.label'))
                    ->placeholder(__('attendee_check_in.search.placeholder'))
                    ->autofocus()
                    ->extraInputAttributes([
                        'autocomplete' => 'off',
                        'x-on:refocus-code-input.window' => '$nextTick(() => $el.focus())',
                    ])
                    ->afterStateUpdated(fn () => $this->search()),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function search(): void
    {
        $raw = trim((string) ($this->data['code'] ?? ''));

        if (mb_strlen($raw) < 4) {
            return;
        }

        $this->candidateBookings = null;
        $this->booking = null;

        $match = $this->resolveBooking($raw);

        if ($match instanceof Booking) {
            $this->loadBooking($match);
        } elseif ($match instanceof Collection) {
            $this->candidateBookings = $match;
        } else {
            Notification::make()
                ->danger()
                ->title(__('attendee_check_in.notifications.not_found_title'))
                ->body(__('attendee_check_in.notifications.not_found_body'))
                ->send();
        }

        $this->data['code'] = null;
        $this->dispatch('refocus-code-input');
    }

    /**
     * Resolves the scanned/typed code to a booking. Returns a single Booking for an
     * exact booking-reference or ticket-number match (both unique), a Collection of
     * candidate bookings when a phone number matches attendees across more than one
     * booking (ambiguous), or null when nothing matches.
     */
    protected function resolveBooking(string $raw): Booking|Collection|null
    {
        $code = strtoupper($raw);

        if ($booking = Booking::query()->where('booking_reference', $code)->first()) {
            return $booking;
        }

        if ($attendee = BookingAttendee::query()->where('ticket_number', $code)->first()) {
            return $attendee->booking;
        }

        $inputDigits = preg_replace('/\D+/', '', $raw);

        if ($inputDigits !== '' && strlen($inputDigits) >= 7) {
            // Country-code prefixes vary (attendees may or may not include one), so
            // compare only the last N digits, where N is the shorter of the two
            // numbers being compared, rather than a fixed-length suffix.
            $matchingBookingIds = BookingAttendee::query()
                ->whereNotNull('phone')
                ->get(['id', 'booking_id', 'phone'])
                ->filter(function (BookingAttendee $attendee) use ($inputDigits) {
                    $storedDigits = preg_replace('/\D+/', '', $attendee->phone);
                    $len = min(strlen($inputDigits), strlen($storedDigits));

                    return $len >= 7 && substr($inputDigits, -$len) === substr($storedDigits, -$len);
                })
                ->pluck('booking_id')
                ->unique();

            if ($matchingBookingIds->count() === 1) {
                return Booking::find($matchingBookingIds->first());
            }

            if ($matchingBookingIds->count() > 1) {
                return Booking::query()
                    ->with(['event', 'timeSlot', 'ticketType'])
                    ->whereIn('id', $matchingBookingIds)
                    ->get();
            }
        }

        return null;
    }

    public function selectCandidateBooking(int $bookingId): void
    {
        $booking = Booking::find($bookingId);

        if ($booking) {
            $this->loadBooking($booking);
        }

        $this->candidateBookings = null;
    }

    protected function loadBooking(Booking $booking): void
    {
        $booking->load(['event', 'timeSlot', 'ticketType', 'attendees.ticketType']);

        if ($booking->status === 'refunded') {
            $this->booking = null;
            $this->candidateBookings = null;

            Notification::make()
                ->danger()
                ->title(__('attendee_check_in.notifications.refunded_title'))
                ->body(__('attendee_check_in.notifications.refunded_body', ['reference' => $booking->booking_reference]))
                ->persistent()
                ->send();

            return;
        }

        if (! $this->passesDateRestriction($booking)) {
            $this->booking = null;
            $this->candidateBookings = null;

            return;
        }

        $this->booking = $booking;
        $this->candidateBookings = null;
        $this->refreshEventStats($booking);
    }

    /**
     * Validates the booking's event date against the "Check-In Date
     * Restriction" general setting. Always surfaces a popup notification
     * when the scanned date doesn't match; in "block" mode it also refuses
     * to load the booking, in "warn" mode staff may still proceed.
     */
    protected function passesDateRestriction(Booking $booking): bool
    {
        if (! (bool) BookingSetting::get('checkin_date_restriction_enabled', false)) {
            return true;
        }

        if (! $booking->event_date) {
            return true;
        }

        $graceDays = (int) BookingSetting::get('checkin_date_grace_days', 0);
        $today = today();
        $eventDate = $booking->event_date->copy()->startOfDay();
        $diffDays = (int) (($eventDate->timestamp - $today->timestamp) / 86400);

        if (abs($diffDays) <= $graceDays) {
            return true;
        }

        $isFuture = $diffDays > 0;
        $bodyKey = $isFuture
            ? 'attendee_check_in.notifications.date_restricted_body_future'
            : 'attendee_check_in.notifications.date_restricted_body_past';
        $formattedDate = $eventDate->format('Y-m-d');

        $mode = BookingSetting::get('checkin_date_restriction_mode', 'block');

        if ($mode === 'warn') {
            Notification::make()
                ->warning()
                ->title(__('attendee_check_in.notifications.date_restricted_warning_title'))
                ->body(__($bodyKey, ['date' => $formattedDate]) . ' ' . __('attendee_check_in.notifications.date_restricted_override_note'))
                ->persistent()
                ->send();

            return true;
        }

        Notification::make()
            ->danger()
            ->title(__('attendee_check_in.notifications.date_restricted_title'))
            ->body(__($bodyKey, ['date' => $formattedDate]))
            ->persistent()
            ->send();

        return false;
    }

    /**
     * Recomputes total vs. checked-in attendee counts across every
     * confirmed booking for the loaded booking's event + event date, so
     * staff see event-wide progress rather than just the scanned booking.
     */
    protected function refreshEventStats(Booking $booking): void
    {
        $totals = BookingAttendee::query()
            ->whereHas('booking', fn ($query) => $query
                ->where('event_id', $booking->event_id)
                ->where('event_date', $booking->event_date)
                ->whereIn('status', ['confirmed', 'checked_in']))
            ->selectRaw('count(*) as total, sum(case when checked_in then 1 else 0 end) as checked_in')
            ->first();

        $this->eventStats = [
            'event_id' => $booking->event_id,
            'event_date' => $booking->event_date->format('Y-m-d'),
            'event_title' => $booking->event?->getTranslations('title') ?? [],
            'total' => (int) ($totals->total ?? 0),
            'checked_in' => (int) ($totals->checked_in ?? 0),
        ];
    }

    public function toggleAttendee(int $attendeeId): void
    {
        if (! $this->booking) {
            return;
        }

        $attendee = $this->booking->attendees->firstWhere('id', $attendeeId);

        if (! $attendee) {
            return;
        }

        $attendee->setCheckedIn(! $attendee->checked_in);
        $this->booking->load('attendees.ticketType');
        $this->refreshEventStats($this->booking);

        Notification::make()
            ->success()
            ->title($attendee->checked_in
                ? __('attendee_check_in.notifications.checked_in_title')
                : __('attendee_check_in.notifications.undo_title'))
            ->body($attendee->getFullName())
            ->send();
    }

    public function checkInAll(): void
    {
        if (! $this->booking) {
            return;
        }

        $remaining = $this->booking->attendees->where('checked_in', false);

        if ($remaining->isEmpty()) {
            return;
        }

        BookingAttendee::query()
            ->whereIn('id', $remaining->pluck('id'))
            ->update(['checked_in' => true, 'checked_in_at' => now()]);

        $this->booking->load('attendees.ticketType');
        $this->refreshEventStats($this->booking);

        Notification::make()
            ->success()
            ->title(__('attendee_check_in.notifications.check_in_all_title'))
            ->body(__('attendee_check_in.notifications.check_in_all_body', ['count' => $remaining->count()]))
            ->send();
    }

    public function resetSearch(): void
    {
        $this->booking = null;
        $this->candidateBookings = null;
        $this->data['code'] = null;
        $this->dispatch('refocus-code-input');
    }

    public function getTitle(): string
    {
        return __('attendee_check_in.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('attendee_check_in.navigation.label');
    }
}

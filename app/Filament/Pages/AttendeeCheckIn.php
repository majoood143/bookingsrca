<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\BookingAttendee;
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
                    ->extraInputAttributes(['autocomplete' => 'off'])
                    ->afterStateUpdated(fn () => $this->search()),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $raw = trim((string) ($this->data['code'] ?? ''));

        $this->candidateBookings = null;
        $this->booking = null;

        if (mb_strlen($raw) < 4) {
            return;
        }

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
        $this->booking = $booking->load(['event', 'timeSlot', 'ticketType', 'attendees.ticketType']);
        $this->candidateBookings = null;
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

<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

// A Thawani checkout session stays payable for 24 hours, far longer than the
// pending-booking hold, so a booking we already cancelled can still be paid.
class ThawaniLatePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TimeSlot $slot;
    protected TicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        BookingSetting::set('thawani.secret_key', 'sk_test');
        BookingSetting::set('thawani.publishable_key', 'pk_test');
        BookingSetting::set('thawani.test_mode', '1');
        BookingSetting::set('pending_booking_expiry_minutes', '5');

        $this->event = Event::create([
            'title' => ['en' => 'Test Event', 'ar' => 'فعالية تجريبية'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => 'test-event',
            'location' => ['en' => 'Test Venue', 'ar' => 'مكان'],
            'organizer' => 'Test Org',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'published',
            'max_attendees' => 1000,
        ]);

        $this->slot = TimeSlot::create([
            'event_id' => $this->event->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'max_attendees' => 1,
            'is_active' => true,
        ]);

        $this->ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => ['en' => 'Adult', 'ar' => 'بالغ'],
            'description' => ['en' => '', 'ar' => ''],
            'price' => 15.00,
            'quantity_available' => 100,
            'is_active' => true,
        ]);
    }

    protected function makeBooking(array $overrides = []): Booking
    {
        $booking = Booking::create(array_merge([
            'event_id' => $this->event->id,
            'time_slot_id' => $this->slot->id,
            'ticket_type_id' => $this->ticketType->id,
            'event_date' => $this->slot->date,
            'quantity' => 1,
            'ticket_price' => 15,
            'services_price' => 0,
            'total_price' => 15,
            'source' => 'online',
            'status' => 'pending',
            'payment_method' => 'thawani',
            'payment_status' => 'pending',
            'payment_session_id' => 'checkout_' . uniqid(),
        ], $overrides));

        BookingAttendee::create([
            'booking_id' => $booking->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_price' => 15,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        return $booking;
    }

    // A booking the expiry sweep gave up on: cancelled, payment marked failed.
    protected function makeExpiredBooking(array $overrides = []): Booking
    {
        return $this->makeBooking(array_merge([
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'cancelled_at' => now()->subMinutes(10),
            'cancellation_source' => 'expired',
        ], $overrides));
    }

    protected function fakeSession(string $status, string $invoice = 'INV-2026'): void
    {
        Http::fake([
            '*checkout/session/*' => Http::response([
                'success' => true,
                'data' => ['payment_status' => $status, 'invoice' => $invoice],
            ]),
        ]);
    }

    public function test_paid_session_confirms_a_pending_booking_and_stores_the_invoice(): void
    {
        $this->fakeSession('paid');
        $booking = $this->makeBooking();

        $this->get(route('payment.callback', ['reference' => $booking->booking_reference]))
            ->assertRedirect(route('booking.success', $booking->booking_reference));

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('INV-2026', $booking->payment_reference);
        $this->assertNotNull($booking->confirmed_at);
        $this->assertSame(1, $booking->payments()->count());
        Mail::assertQueued(BookingConfirmation::class, 1);
    }

    public function test_paid_session_revives_a_booking_the_expiry_sweep_cancelled(): void
    {
        $this->fakeSession('paid');
        $booking = $this->makeExpiredBooking();

        $this->get(route('payment.callback', ['reference' => $booking->booking_reference]))
            ->assertRedirect(route('booking.success', $booking->booking_reference));

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertNull($booking->cancelled_at);
        $this->assertNull($booking->cancellation_source);
        $this->assertNotNull($booking->confirmed_at);
        $this->assertSame(1, $booking->payments()->count());
        $this->assertSame(1, $this->slot->fresh()->current_bookings);
        $this->assertSame(1, $this->ticketType->fresh()->quantity_sold);
        Mail::assertQueued(BookingConfirmation::class, 1);
    }

    public function test_bookings_cancelled_before_the_source_column_existed_are_left_alone(): void
    {
        $this->fakeSession('paid');
        // Legacy system cancellation: no source recorded.
        $booking = $this->makeExpiredBooking(['cancellation_source' => null]);

        $this->get(route('payment.callback', ['reference' => $booking->booking_reference]));
        $this->artisan('bookings:recover-late-payments')->assertSuccessful();

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('failed', $booking->payment_status);
        $this->assertSame(0, $booking->payments()->count());
        Mail::assertNothingQueued();
    }

    public function test_reconciling_twice_does_not_double_count_or_re_email(): void
    {
        $this->fakeSession('paid');
        $booking = $this->makeExpiredBooking();
        $thawani = app(\App\Services\ThawaniService::class);

        $thawani->reconcileBooking($booking);
        $thawani->reconcileBooking($booking->fresh());

        $this->assertSame(1, $this->slot->fresh()->current_bookings);
        $this->assertSame(1, $booking->payments()->count());
        Mail::assertQueued(BookingConfirmation::class, 1);
    }

    public function test_paid_booking_whose_seats_were_resold_stays_cancelled_and_is_flagged(): void
    {
        $this->fakeSession('paid');
        $expired = $this->makeExpiredBooking();
        // Someone else took the only seat (slot capacity is 1) after the hold lapsed.
        $this->makeBooking(['status' => 'confirmed', 'payment_status' => 'paid', 'confirmed_at' => now()]);

        $this->get(route('payment.callback', ['reference' => $expired->booking_reference]))
            ->assertRedirect(route('event.booking', $this->event->slug))
            ->assertSessionHas('error');

        $expired->refresh();
        $this->assertSame('cancelled', $expired->status);
        $this->assertSame('paid', $expired->payment_status);   // shows up under "paid but cancelled"
        $this->assertSame(0, $expired->payments()->count());
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_paid_booking_an_admin_cancelled_is_not_revived(): void
    {
        $this->fakeSession('paid');
        $booking = $this->makeBooking([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_source' => 'admin',
        ]);

        $this->get(route('payment.callback', ['reference' => $booking->booking_reference]));

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_cancel_url_with_a_paid_session_confirms_instead_of_cancelling(): void
    {
        $this->fakeSession('paid');
        $booking = $this->makeBooking();

        $this->get(route('payment.cancel', ['reference' => $booking->booking_reference]))
            ->assertRedirect(route('booking.success', $booking->booking_reference));

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_cancel_url_with_an_unpaid_session_cancels_as_customer(): void
    {
        $this->fakeSession('unpaid');
        $booking = $this->makeBooking();

        $this->get(route('payment.cancel', ['reference' => $booking->booking_reference]));

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('customer', $booking->cancellation_source);
        $this->assertSame('failed', $booking->payment_status);
    }

    public function test_cancel_url_still_cancels_when_thawani_cannot_be_reached(): void
    {
        Http::fake(['*' => Http::response('down', 500)]);
        $booking = $this->makeBooking();

        $this->get(route('payment.cancel', ['reference' => $booking->booking_reference]));

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_unpaid_callback_keeps_the_booking_pending(): void
    {
        $this->fakeSession('unpaid');
        $booking = $this->makeBooking();

        $this->get(route('payment.callback', ['reference' => $booking->booking_reference]))
            ->assertRedirect(route('event.booking', $this->event->slug));

        $booking->refresh();
        $this->assertSame('pending', $booking->status);
        $this->assertSame('pending', $booking->payment_status);
    }

    public function test_expiry_sweep_records_expired_as_the_source(): void
    {
        $this->fakeSession('unpaid');
        $booking = $this->makeBooking();
        Booking::whereKey($booking->id)->update(['created_at' => now()->subMinutes(30)]);

        $this->artisan('bookings:cancel-expired')->assertSuccessful();

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('expired', $booking->cancellation_source);
    }

    public function test_recovery_command_revives_paid_bookings_and_ignores_the_rest(): void
    {
        $this->fakeSession('paid');
        $paid = $this->makeExpiredBooking();
        $notThawani = $this->makeExpiredBooking(['payment_method' => 'ccavenue', 'payment_session_id' => 'cc-1']);
        $tooOld = $this->makeExpiredBooking();
        Booking::whereKey($tooOld->id)->update(['created_at' => now()->subHours(30)]);

        $this->artisan('bookings:recover-late-payments')->assertSuccessful();

        $this->assertSame('confirmed', $paid->fresh()->status);
        $this->assertSame('cancelled', $notThawani->fresh()->status);
        $this->assertSame('cancelled', $tooOld->fresh()->status);
    }

    public function test_recovery_command_leaves_unpaid_bookings_cancelled(): void
    {
        $this->fakeSession('unpaid');
        $booking = $this->makeExpiredBooking();

        $this->artisan('bookings:recover-late-payments')->assertSuccessful();

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_repair_command_dry_run_changes_nothing_and_apply_fixes_bookings(): void
    {
        $this->fakeSession('paid');
        $cancelled = $this->makeExpiredBooking();
        // An admin flipped the status dropdown to "confirmed": no timestamp, payment, counters.
        $manual = $this->makeBooking([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'cancelled_at' => now()->subHour(),
            'cancellation_source' => 'expired',
        ]);
        $this->slot->update(['max_attendees' => 5]);

        $this->artisan('bookings:repair-thawani')->assertSuccessful();

        $this->assertSame('cancelled', $cancelled->fresh()->status);
        $this->assertNull($manual->fresh()->confirmed_at);

        $this->artisan('bookings:repair-thawani', ['--apply' => true])->assertSuccessful();

        $cancelled->refresh();
        $manual->refresh();
        $this->assertSame('confirmed', $cancelled->status);
        $this->assertSame('confirmed', $manual->status);
        $this->assertNotNull($manual->confirmed_at);
        $this->assertNull($manual->cancelled_at);
        $this->assertSame('INV-2026', $manual->payment_reference);
        $this->assertSame(1, $manual->payments()->count());
        $this->assertSame(2, $this->slot->fresh()->current_bookings);
        // No --notify, so nobody is emailed for the repair.
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_only_system_cancellations_count_as_recoverable(): void
    {
        $this->assertTrue($this->makeExpiredBooking()->isSystemCancelled());
        $this->assertTrue($this->makeBooking(['status' => 'cancelled', 'cancellation_source' => 'customer'])->isSystemCancelled());
        $this->assertFalse($this->makeBooking(['status' => 'cancelled', 'cancellation_source' => 'admin'])->isSystemCancelled());
        // Cancelled before the source column existed: never treated as recoverable.
        $this->assertFalse($this->makeExpiredBooking(['cancellation_source' => null])->isSystemCancelled());
        $this->assertFalse($this->makeBooking()->isSystemCancelled());
    }
}

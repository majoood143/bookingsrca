<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Livewire\EventBooking;
use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\Kiosk;
use App\Models\PaymentGatewayLog;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderGatewayLog;
use App\Models\PosProduct;
use App\Models\TicketType;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\CCAvenueService;
use App\Services\NboService;
use App\Services\SeatAllocationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

// Regression tests for the 2026-10-28 audit (.claude/skills/booking-platform/AUDIT.md).
class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected TimeSlot $slot;

    protected TicketType $adult;

    protected TicketType $child;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        BookingSetting::set('ccavenue.working_key', str_repeat('k', 32));

        $this->event = $this->makeEvent('test-event');
        $this->slot = TimeSlot::create([
            'event_id' => $this->event->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'max_attendees' => 50,
            'is_active' => true,
        ]);
        $this->adult = $this->makeTicketType($this->event, 'Adult', 5.00, 100);
        $this->child = $this->makeTicketType($this->event, 'Child', 2.00, 2);
    }

    private function makeEvent(string $slug): Event
    {
        return Event::create([
            'title' => ['en' => 'Test Event', 'ar' => 'فعالية'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => $slug,
            'location' => ['en' => 'Venue', 'ar' => 'مكان'],
            'organizer' => 'Org',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'published',
            'max_attendees' => 1000,
        ]);
    }

    private function makeTicketType(Event $event, string $name, float $price, int $available): TicketType
    {
        return TicketType::create([
            'event_id' => $event->id,
            'name' => ['en' => $name, 'ar' => $name],
            'description' => ['en' => '', 'ar' => ''],
            'price' => $price,
            'quantity_available' => $available,
            'is_active' => true,
        ]);
    }

    // One booking whose first (primary) type is Adult, with the given attendee types.
    private function makeBooking(array $attendeeTypes, string $status = 'pending', array $extra = []): Booking
    {
        $booking = Booking::create(array_merge([
            'event_id' => $this->event->id,
            'time_slot_id' => $this->slot->id,
            'ticket_type_id' => $attendeeTypes[0]->id,
            'event_date' => $this->slot->date,
            'quantity' => count($attendeeTypes),
            'ticket_price' => 5,
            'services_price' => 0,
            'total_price' => 5,
            'source' => 'online',
            'status' => $status,
            'payment_method' => 'ccavenue',
            'payment_status' => 'pending',
        ], $extra));

        foreach ($attendeeTypes as $type) {
            BookingAttendee::create([
                'booking_id' => $booking->id,
                'ticket_type_id' => $type->id,
                'ticket_price' => $type->price,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '91234567',
            ]);
        }

        return $booking->fresh();
    }

    private function ccavenueSuccess(Booking $booking): void
    {
        $orderId = str_replace('-', '', $booking->booking_reference);
        $booking->update(['payment_session_id' => $orderId]);

        $encResp = app(CCAvenueService::class)->encrypt(http_build_query([
            'order_id' => $orderId,
            'order_status' => 'Success',
            'amount' => (string) $booking->total_price,
            'tracking_id' => 'TRACK1',
        ]));

        $this->post(route('payment.callback.ccavenue'), ['encResp' => $encResp]);
    }

    public function test_payment_method_cannot_be_set_from_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(EventBooking::class, ['event' => $this->event])
            ->set('selectedPaymentMethod', 'cash');
    }

    public function test_ticket_type_from_another_event_is_rejected(): void
    {
        $other = $this->makeEvent('other-event');
        $cheap = $this->makeTicketType($other, 'Free', 0, 100);

        $this->expectException(Exception::class);

        DB::transaction(fn () => app(SeatAllocationService::class)->lockAndPrice(
            timeSlotId: $this->slot->id,
            eventId: $this->event->id,
            ticketQuantities: [$cheap->id => 2],
        ));
    }

    public function test_stock_is_counted_per_ticket_type_on_mixed_bookings(): void
    {
        $this->makeBooking([$this->adult, $this->child, $this->child]);

        $this->assertSame(1, $this->adult->getActiveSoldQuantity());
        $this->assertSame(2, $this->child->getActiveSoldQuantity());
        $this->assertSame(0, $this->child->getRemainingQuantity());
    }

    public function test_repeated_ccavenue_callback_confirms_once(): void
    {
        $booking = $this->makeBooking([$this->adult, $this->child]);

        $this->ccavenueSuccess($booking);
        $this->ccavenueSuccess($booking->fresh());

        $booking->refresh();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(1, $booking->payments()->count());
        $this->assertSame(1, $this->adult->fresh()->quantity_sold);
        $this->assertSame(1, $this->child->fresh()->quantity_sold);
    }

    public function test_late_ccavenue_callback_does_not_revive_a_booking_whose_seats_were_resold(): void
    {
        $late = $this->makeBooking([$this->child], 'cancelled', ['cancellation_source' => 'expired', 'payment_status' => 'failed']);
        $this->makeBooking([$this->child, $this->child], 'confirmed');

        $this->ccavenueSuccess($late);

        $late->refresh();
        $this->assertSame('cancelled', $late->status);
        $this->assertSame('paid', $late->payment_status); // flagged for a manual refund
    }

    public function test_forged_shop_thawani_webhook_does_not_mark_order_paid(): void
    {
        Http::fake(['*' => Http::response(['data' => ['payment_status' => 'unpaid']])]);

        $product = PosProduct::create([
            'name' => 'Honey', 'slug' => 'honey', 'sku' => 'H1', 'price' => 3, 'tax_rate' => 0,
            'stock_quantity' => 5, 'is_active' => true, 'is_published_online' => true,
        ]);
        $customer = PosCustomer::create(['type' => 'individual', 'name' => 'Buyer', 'email' => 'b@example.com', 'mobile' => '99999999']);
        $order = PosOrder::create([
            'order_number' => PosOrder::nextOrderNumber(),
            'customer_id' => $customer->id,
            'contact_name' => 'Buyer',
            'contact_email' => 'b@example.com',
            'contact_mobile' => '99999999',
            'subtotal' => 3, 'tax_total' => 0, 'grand_total' => 3,
            'payment_reference' => 'sess_123',
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Honey', 'quantity' => 1, 'unit_price' => 3, 'line_total' => 3]);

        $this->postJson(route('shop.payment.webhook.thawani'), [
            'event_type' => 'payment_completed',
            'data' => ['session_id' => 'sess_123', 'payment_ref' => 'FAKE'],
        ])->assertOk();

        $this->assertFalse($order->fresh()->isPaid());
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_shop_order_is_only_paid_once(): void
    {
        $product = PosProduct::create([
            'name' => 'Honey', 'slug' => 'honey', 'sku' => 'H1', 'price' => 3, 'tax_rate' => 0,
            'stock_quantity' => 5, 'is_active' => true, 'is_published_online' => true,
        ]);
        $customer = PosCustomer::create(['type' => 'individual', 'name' => 'Buyer', 'email' => 'b@example.com', 'mobile' => '99999999']);
        $order = PosOrder::create([
            'order_number' => PosOrder::nextOrderNumber(),
            'customer_id' => $customer->id,
            'contact_name' => 'Buyer',
            'contact_email' => 'b@example.com',
            'contact_mobile' => '99999999',
            'subtotal' => 3, 'tax_total' => 0, 'grand_total' => 3,
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Honey', 'quantity' => 1, 'unit_price' => 3, 'line_total' => 3]);

        // Two stale copies, like the browser callback and the webhook each loading the order.
        $first = PosOrder::find($order->id);
        $second = PosOrder::find($order->id);
        $first->markPaid('thawani', 'REF');
        $second->markPaid('thawani', 'REF');

        $this->assertSame(4, $product->fresh()->stock_quantity);
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_nbo_payment_start_sends_the_same_request_for_bookings_and_orders(): void
    {
        BookingSetting::set('nbo.tranportal_id', 'TID');
        BookingSetting::set('nbo.tranportal_password', 'PASS');
        BookingSetting::set('nbo.resource_key', str_repeat('r', 32));
        Http::fake(['*' => Http::response([['status' => '1', 'result' => 'PAY123:https://nbo.test/pay']])]);

        $nbo = app(NboService::class);
        $booking = $this->makeBooking([$this->adult]);
        $customer = PosCustomer::create(['type' => 'individual', 'name' => 'Buyer', 'email' => 'b@example.com', 'mobile' => '99999999']);
        $order = PosOrder::create([
            'order_number' => PosOrder::nextOrderNumber(), 'customer_id' => $customer->id,
            'contact_name' => 'Jane Buyer', 'contact_email' => 'b@example.com', 'contact_mobile' => '+968 9999',
            'subtotal' => 3, 'tax_total' => 0, 'grand_total' => 3,
        ]);

        $this->assertSame('https://nbo.test/pay?PaymentID=PAY123', $nbo->initiatePayment($booking));
        $this->assertSame('PAY123', $booking->fresh()->payment_session_id);

        $this->assertSame('https://nbo.test/pay?PaymentID=PAY123', $nbo->initiateOrderPayment($order));
        $this->assertSame('PAY123', $order->fresh()->payment_reference);
        $this->assertSame('nbo', $order->fresh()->payment_method);

        $sent = collect(Http::recorded())->map(fn ($pair) => $nbo->decrypt($pair[0]->data()[0]['trandata']));
        $this->assertSame('5.000', $sent[0]['amt']);
        $this->assertSame(route('payment.callback.nbo'), $sent[0]['responseURL']);
        $this->assertSame('John', $sent[0]['billingInfo']['firstName']);
        $this->assertSame('3.000', $sent[1]['amt']);
        $this->assertSame(route('shop.payment.callback.nbo'), $sent[1]['responseURL']);
        $this->assertSame('Buyer', $sent[1]['billingInfo']['lastName']);
        $this->assertSame('9689999', $sent[1]['udf2']);
        $this->assertSame(1, PaymentGatewayLog::where('event', 'initiate_payment')->count());
        $this->assertSame(1, PosOrderGatewayLog::where('event', 'initiate_payment')->count());
    }

    public function test_kiosk_with_device_token_only_opens_for_the_device(): void
    {
        $kiosk = Kiosk::create([
            'name' => 'Gate', 'code' => 'gate', 'event_id' => $this->event->id, 'is_active' => true,
            'idle_timeout_seconds' => 90, 'enabled_payment_methods' => ['wallet'], 'device_token' => 'device-secret',
        ]);

        $this->get('/kiosk/gate')->assertForbidden();
        $this->get('/kiosk/gate?token=wrong')->assertForbidden();
        $this->get('/kiosk/gate?token=device-secret')->assertOk();
        $this->get('/kiosk/gate')->assertOk(); // remembered in the session

        $kiosk->update(['device_token' => 'rotated']);
        $this->get('/kiosk/gate')->assertForbidden(); // rotating the token logs old devices out
    }

    public function test_guest_checkout_does_not_overwrite_an_existing_customer(): void
    {
        $existing = PosCustomer::create(['type' => 'individual', 'name' => 'Real Owner', 'email' => 'owner@example.com', 'mobile' => '91111111']);

        $found = PosCustomer::findOrCreateForGuestCheckout(['name' => 'Someone Else', 'email' => 'owner@example.com', 'mobile' => '92222222']);

        $this->assertTrue($found->is($existing));
        $this->assertSame('Real Owner', $existing->fresh()->name);
        $this->assertSame('91111111', $existing->fresh()->mobile);
    }

    public function test_booking_submissions_are_rate_limited_per_session(): void
    {
        $component = Livewire::test(EventBooking::class, ['event' => $this->event]);

        foreach (range(1, 5) as $i) {
            RateLimiter::hit('booking-session:'.session()->getId(), 600);
        }

        // Stopped before validation: an unfilled form would otherwise report errors.
        $component->call('submitBooking')->assertHasNoErrors();

        $this->assertSame(0, Booking::count());
    }

    public function test_existing_super_admin_keeps_installer_closed_without_marker_file(): void
    {
        $marker = storage_path('installed');
        $backup = File::exists($marker) ? File::get($marker) : null;

        try {
            File::delete($marker);
            $this->assertFalse(EnsureAppIsInstalled::isInstalled());

            Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
            User::create(['name' => 'Admin', 'email' => 'a@example.com', 'password' => 'secret123'])->assignRole('super_admin');

            $this->assertTrue(EnsureAppIsInstalled::isInstalled());
        } finally {
            if ($backup !== null) {
                File::put($marker, $backup);
            }
        }
    }
}

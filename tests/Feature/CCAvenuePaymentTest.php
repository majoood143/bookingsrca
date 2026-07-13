<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\PaymentGatewayLog;
use App\Models\TicketType;
use App\Models\TimeSlot;
use App\Services\CCAvenueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CCAvenuePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TimeSlot $slot;
    protected TicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        BookingSetting::set('ccavenue.merchant_id', 'TESTMERCHANT');
        BookingSetting::set('ccavenue.access_code', 'TESTACCESSCODE');
        BookingSetting::set('ccavenue.working_key', str_repeat('k', 32));
        BookingSetting::set('ccavenue.test_mode', '1');

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
            'max_attendees' => 50,
            'is_active' => true,
        ]);

        $this->ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => ['en' => 'Adult', 'ar' => 'بالغ'],
            'description' => ['en' => '', 'ar' => ''],
            'price' => 5.00,
            'quantity_available' => 100,
            'is_active' => true,
        ]);
    }

    protected function makeBooking(float $total = 5.00): Booking
    {
        $booking = Booking::create([
            'event_id' => $this->event->id,
            'time_slot_id' => $this->slot->id,
            'ticket_type_id' => $this->ticketType->id,
            'event_date' => $this->slot->date,
            'quantity' => 1,
            'ticket_price' => $total,
            'services_price' => 0,
            'total_price' => $total,
            'source' => 'online',
            'status' => 'pending',
            'payment_method' => 'ccavenue',
            'payment_status' => 'pending',
        ]);

        BookingAttendee::create([
            'booking_id' => $booking->id,
            'ticket_type_id' => $this->ticketType->id,
            'ticket_price' => $total,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '91234567',
        ]);

        return $booking->fresh();
    }

    public function test_encrypt_decrypt_round_trip(): void
    {
        $service = app(CCAvenueService::class);

        $plainText = 'order_id=BK-TEST1234&order_status=success&amount=5.000';
        $encrypted = $service->encrypt($plainText);

        $this->assertNotSame($plainText, $encrypted);
        $this->assertSame($plainText, $service->decrypt($encrypted));

        // Random IV means repeated calls produce different ciphertext...
        $encryptedAgain = $service->encrypt($plainText);
        $this->assertNotSame($encrypted, $encryptedAgain);
        // ...but still decrypt back to the same plaintext.
        $this->assertSame($plainText, $service->decrypt($encryptedAgain));
    }

    public function test_build_redirect_payload_shape_and_side_effects(): void
    {
        $booking = $this->makeBooking();
        $service = app(CCAvenueService::class);

        $payload = $service->buildRedirectPayload($booking);

        $this->assertArrayHasKey('url', $payload);
        $this->assertArrayHasKey('encRequest', $payload);
        $this->assertArrayHasKey('access_code', $payload);
        $this->assertSame('TESTACCESSCODE', $payload['access_code']);

        $booking->refresh();
        // order_id (and payment_session_id) is booking_reference with the
        // hyphen stripped, since CCAvenue rejects non-alphanumeric order_ids.
        $expectedOrderId = str_replace('-', '', $booking->booking_reference);
        $this->assertSame($expectedOrderId, $booking->payment_session_id);
        $this->assertStringNotContainsString('-', $booking->payment_session_id);

        $log = PaymentGatewayLog::where('booking_id', $booking->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('ccavenue', $log->gateway);
        $this->assertSame('initiate_payment', $log->event);

        // The encrypted request round-trips back to the plain param string
        // and carries the booking's order_id/amount.
        $decrypted = $service->decrypt($payload['encRequest']);
        parse_str($decrypted, $data);
        $this->assertSame($expectedOrderId, $data['order_id']);
        $this->assertSame('5.000', $data['amount']);
    }

    public function test_is_configured_reflects_settings(): void
    {
        $this->assertTrue(app(CCAvenueService::class)->isConfigured());

        BookingSetting::set('ccavenue.working_key', '');
        $this->assertFalse(app(CCAvenueService::class)->isConfigured());
    }

    public function test_routes_are_registered(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('payment.redirect.ccavenue'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('payment.callback.ccavenue'));
    }

    public function test_redirect_route_renders_auto_submit_form(): void
    {
        $booking = $this->makeBooking();

        $response = $this->get(route('payment.redirect.ccavenue', $booking->booking_reference));

        $response->assertOk();
        $response->assertSee('id="ccavenue-redirect-form"', false);
        $response->assertSee('name="encRequest"', false);
        $response->assertSee('name="access_code"', false);
    }

    public function test_callback_post_is_not_blocked_by_csrf(): void
    {
        $response = $this->post(route('payment.callback.ccavenue'), []);

        // Missing encResp -> handled gracefully, not a 419 CSRF rejection.
        $response->assertStatus(302);
        $this->assertNotSame(419, $response->getStatusCode());
    }

    /**
     * Simulate having already gone through buildRedirectPayload(): sets
     * payment_session_id to the alphanumeric order_id CCAvenue would echo
     * back in its callback, and returns that order_id for use in the test.
     */
    protected function initiateAndGetOrderId(Booking $booking): string
    {
        $orderId = str_replace('-', '', $booking->booking_reference);
        $booking->update(['payment_session_id' => $orderId]);

        return $orderId;
    }

    public function test_successful_callback_confirms_booking(): void
    {
        $booking = $this->makeBooking(5.00);
        $orderId = $this->initiateAndGetOrderId($booking);
        $service = app(CCAvenueService::class);

        $encResp = $service->encrypt(http_build_query([
            'order_id' => $orderId,
            'order_status' => 'Success',
            'amount' => '5.00',
            'tracking_id' => 'TRACK123',
            'bank_ref_no' => 'BANKREF456',
        ]));

        $response = $this->post(route('payment.callback.ccavenue'), ['encResp' => $encResp]);

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('TRACK123', $booking->payment_reference);
        $response->assertRedirect(route('booking.success', $booking->booking_reference));
    }

    public function test_callback_with_tampered_amount_does_not_confirm_booking(): void
    {
        $booking = $this->makeBooking(5.00);
        $orderId = $this->initiateAndGetOrderId($booking);
        $service = app(CCAvenueService::class);

        $encResp = $service->encrypt(http_build_query([
            'order_id' => $orderId,
            'order_status' => 'Success',
            'amount' => '0.01', // tampered
        ]));

        $this->post(route('payment.callback.ccavenue'), ['encResp' => $encResp]);

        $booking->refresh();
        $this->assertSame('failed', $booking->payment_status);
        $this->assertNotSame('confirmed', $booking->status);
    }

    public function test_aborted_callback_cancels_booking(): void
    {
        $booking = $this->makeBooking(5.00);
        $orderId = $this->initiateAndGetOrderId($booking);
        $service = app(CCAvenueService::class);

        $encResp = $service->encrypt(http_build_query([
            'order_id' => $orderId,
            'order_status' => 'Aborted',
            'amount' => '5.00',
        ]));

        $this->post(route('payment.callback.ccavenue'), ['encResp' => $encResp]);

        $booking->refresh();
        $this->assertSame('failed', $booking->payment_status);
        $this->assertSame('cancelled', $booking->status);
    }

    public function test_unrecognized_status_marks_payment_failed_without_cancelling(): void
    {
        $booking = $this->makeBooking(5.00);
        $orderId = $this->initiateAndGetOrderId($booking);
        $service = app(CCAvenueService::class);

        $encResp = $service->encrypt(http_build_query([
            'order_id' => $orderId,
            'order_status' => 'invalid',
            'amount' => '5.00',
        ]));

        $this->post(route('payment.callback.ccavenue'), ['encResp' => $encResp]);

        $booking->refresh();
        $this->assertSame('failed', $booking->payment_status);
        $this->assertSame('pending', $booking->status);
    }
}

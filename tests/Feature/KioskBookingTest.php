<?php

namespace Tests\Feature;

use App\Livewire\Kiosk\KioskBooking;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Kiosk;
use App\Models\KioskCard;
use App\Models\TicketType;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KioskBookingTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TimeSlot $slot;
    protected TicketType $ticketType;
    protected Kiosk $kiosk;

    protected function setUp(): void
    {
        parent::setUp();

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
            'price' => 2.00,
            'quantity_available' => 100,
            'is_active' => true,
        ]);

        $this->kiosk = Kiosk::create([
            'name' => 'Test Kiosk',
            'code' => 'test-kiosk',
            'event_id' => $this->event->id,
            'is_active' => true,
            'idle_timeout_seconds' => 90,
            'enabled_payment_methods' => ['wallet', 'pay_at_counter'],
            'reader_connected' => true,
        ]);
    }

    protected function fillAttendeeZero($test, string $suffix): void
    {
        $test->set('attendees.0.first_name', 'John');
        $test->set('attendees.0.last_name', 'Doe');
        $test->set('attendees.0.email', "john{$suffix}@example.com");
        $test->set('attendees.0.phone', '+96812345678');
        $test->set('attendees.0.gender', 'male');
        $test->set('attendees.0.nationality', 'OM');
        $test->set('attendees.0.identity_number', "ID{$suffix}");
    }

    protected function driveToPaymentStep($suffix)
    {
        $test = Livewire::test(KioskBooking::class, ['kiosk' => $this->kiosk]);
        $test->assertSet('step', 1);

        $test->call('selectDate', $this->slot->date->format('Y-m-d'));
        $test->assertSet('step', 2);

        $test->call('selectSlot', $this->slot->id);
        $test->assertSet('step', 3);

        $test->call('incrementQuantity', $this->ticketType->id);
        $test->call('nextStep');

        if ($test->get('step') === 4) {
            $test->call('nextStep');
        }
        $test->assertSet('step', 5);

        $this->fillAttendeeZero($test, $suffix);

        $test->call('goToPaymentStep');
        $test->assertHasNoErrors();
        $test->assertSet('step', 6);

        return $test;
    }

    public function test_wallet_payment_deducts_balance_and_confirms_booking(): void
    {
        $card = KioskCard::create(['uid' => 'CARD1', 'balance' => 10, 'status' => 'active']);

        $test = $this->driveToPaymentStep('w1');
        $test->call('selectWalletPayment');
        $test->assertSet('awaitingCardTap', true);

        $test->call('onCardTapped', 'CARD1');
        $test->assertHasNoErrors();
        $test->assertSet('step', 7);

        $booking = Booking::latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertSame('kiosk', $booking->source);
        $this->assertSame('kiosk_wallet', $booking->payment_method);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame($this->kiosk->id, $booking->kiosk_id);
        $this->assertEquals(2.00, (float) $booking->total_price);

        $card->refresh();
        $this->assertEquals(8.00, (float) $card->balance);

        $txn = $card->transactions()->latest('id')->first();
        $this->assertSame('payment', $txn->type);
        $this->assertEquals(-2.00, (float) $txn->amount);
        $this->assertEquals(8.00, (float) $txn->balance_after);
        $this->assertSame($booking->id, $txn->booking_id);
        $this->assertSame($this->kiosk->id, $txn->kiosk_id);
    }

    public function test_wallet_payment_with_insufficient_balance_does_not_create_booking(): void
    {
        $card = KioskCard::create(['uid' => 'CARD2', 'balance' => 0.50, 'status' => 'active']);

        $test = $this->driveToPaymentStep('w2');
        $test->call('selectWalletPayment');

        $bookingCountBefore = Booking::count();
        $test->call('onCardTapped', 'CARD2');

        $test->assertSet('step', 6);
        $this->assertSame($bookingCountBefore, Booking::count());

        $card->refresh();
        $this->assertEquals(0.50, (float) $card->balance);
        $this->assertSame(0, $card->transactions()->count());
    }

    public function test_wallet_payment_with_blocked_card_does_not_create_booking(): void
    {
        KioskCard::create(['uid' => 'CARD3', 'balance' => 100, 'status' => 'blocked']);

        $test = $this->driveToPaymentStep('w3');
        $test->call('selectWalletPayment');

        $bookingCountBefore = Booking::count();
        $test->call('onCardTapped', 'CARD3');

        $test->assertSet('step', 6);
        $this->assertSame($bookingCountBefore, Booking::count());
    }

    public function test_unknown_card_uid_does_not_create_booking(): void
    {
        $test = $this->driveToPaymentStep('w4');
        $test->call('selectWalletPayment');

        $bookingCountBefore = Booking::count();
        $test->call('onCardTapped', 'DOES-NOT-EXIST');

        $test->assertSet('step', 6);
        $this->assertSame($bookingCountBefore, Booking::count());
    }

    public function test_pay_at_counter_creates_pending_booking(): void
    {
        $test = $this->driveToPaymentStep('c1');
        $test->call('payAtCounter');
        $test->assertHasNoErrors();
        $test->assertSet('step', 7);

        $booking = Booking::latest('id')->first();
        $this->assertSame('kiosk', $booking->source);
        $this->assertSame('cash', $booking->payment_method);
        $this->assertSame('pending', $booking->payment_status);
        $this->assertSame('pending', $booking->status);
        $this->assertSame($this->kiosk->id, $booking->kiosk_id);
    }

    public function test_card_tap_is_ignored_when_not_awaiting_one(): void
    {
        KioskCard::create(['uid' => 'CARD5', 'balance' => 100, 'status' => 'active']);

        $test = $this->driveToPaymentStep('w5');
        // Never called selectWalletPayment(), so awaitingCardTap is false.
        $bookingCountBefore = Booking::count();
        $test->call('onCardTapped', 'CARD5');

        $this->assertSame($bookingCountBefore, Booking::count());
        $test->assertSet('step', 6);
    }

    public function test_inactive_kiosk_does_not_expose_booking_flow(): void
    {
        $this->kiosk->update(['is_active' => false]);

        $test = Livewire::test(KioskBooking::class, ['kiosk' => $this->kiosk]);
        $test->assertSee(__('kiosk_booking.inactive.heading'));
        $test->assertDontSee('selectDate');
    }
}

<?php

namespace Tests\Feature;

use App\Models\BookingAttendee;
use App\Models\Event;
use App\Models\PosProduct;
use App\Models\PosTerminal;
use App\Models\TicketType;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\PosShiftService;
use App\Services\PosTransactionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected TimeSlot $slot;
    protected TicketType $ticketType;
    protected PosTerminal $terminal;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::create([
            'title' => ['en' => 'Test Event', 'ar' => 'فعالية'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => 'test-event',
            'location' => ['en' => 'Venue', 'ar' => 'مكان'],
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
            'max_attendees' => 5,
            'is_active' => true,
        ]);

        $this->ticketType = TicketType::create([
            'event_id' => $this->event->id,
            'name' => ['en' => 'Adult', 'ar' => 'بالغ'],
            'description' => ['en' => '', 'ar' => ''],
            'price' => 10,
            'quantity_available' => 5,
            'is_active' => true,
        ]);

        $this->terminal = PosTerminal::create(['name' => 'Gate 1', 'is_active' => true]);
        $this->cashier = User::factory()->create();
    }

    public function test_ticket_and_merchandise_sale_in_one_transaction(): void
    {
        $product = PosProduct::create([
            'name' => 'Water Bottle',
            'sku' => 'WB-1',
            'price' => 1.000,
            'tax_rate' => 5,
            'stock_quantity' => 10,
        ]);

        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);

        $transaction = app(PosTransactionService::class)->createTransaction(
            shift: $shift,
            ticketLines: [[
                'event_id' => $this->event->id,
                'time_slot_id' => $this->slot->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 2,
            ]],
            productLines: [[
                'product_id' => $product->id,
                'quantity' => 3,
            ]],
            paymentLines: [[
                'method' => 'cash',
                'amount' => 23.15, // 2*10 ticket + (3*1 + 3*1*0.05 tax) merch = 20 + 3.15
            ]],
        );

        $this->assertSame('completed', $transaction->status);
        $this->assertEquals(23.150, (float) $transaction->grand_total);
        $this->assertEquals(0.150, (float) $transaction->tax_total);
        $this->assertCount(3, $transaction->items); // 2 attendees + 1 product line

        $this->assertSame(2, BookingAttendee::count());
        $this->assertSame('pos', BookingAttendee::first()->booking->source);
        $this->assertSame('confirmed', BookingAttendee::first()->booking->status);

        $this->ticketType->refresh();
        $this->assertSame(3, $this->ticketType->getRemainingQuantity());

        $product->refresh();
        $this->assertSame(7, $product->stock_quantity);
        $this->assertSame(1, $product->stockMovements()->count());
        $this->assertSame(-3, $product->stockMovements()->first()->quantity);
    }

    public function test_overselling_tickets_beyond_capacity_throws_and_rolls_back(): void
    {
        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);

        $this->expectException(Exception::class);

        try {
            app(PosTransactionService::class)->createTransaction(
                shift: $shift,
                ticketLines: [[
                    'event_id' => $this->event->id,
                    'time_slot_id' => $this->slot->id,
                    'ticket_type_id' => $this->ticketType->id,
                    'quantity' => 10, // only 5 available
                ]],
                productLines: [],
                paymentLines: [['method' => 'cash', 'amount' => 100]],
            );
        } finally {
            $this->assertSame(0, BookingAttendee::count());
            $this->assertSame(0, \App\Models\PosTransaction::count());
        }
    }

    public function test_overselling_product_stock_throws_and_rolls_back(): void
    {
        $product = PosProduct::create([
            'name' => 'Water Bottle',
            'sku' => 'WB-2',
            'price' => 1,
            'stock_quantity' => 2,
        ]);

        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);

        $this->expectException(Exception::class);

        try {
            app(PosTransactionService::class)->createTransaction(
                shift: $shift,
                ticketLines: [],
                productLines: [['product_id' => $product->id, 'quantity' => 5]],
                paymentLines: [['method' => 'cash', 'amount' => 100]],
            );
        } finally {
            $product->refresh();
            $this->assertSame(2, $product->stock_quantity);
        }
    }

    public function test_mismatched_payment_total_throws(): void
    {
        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);

        $this->expectException(Exception::class);

        app(PosTransactionService::class)->createTransaction(
            shift: $shift,
            ticketLines: [[
                'event_id' => $this->event->id,
                'time_slot_id' => $this->slot->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 1,
            ]],
            productLines: [],
            paymentLines: [['method' => 'cash', 'amount' => 5]], // ticket costs 10
        );
    }

    public function test_sale_against_a_closed_shift_is_rejected(): void
    {
        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);
        app(PosShiftService::class)->close($shift, 50);

        $this->expectException(Exception::class);

        app(PosTransactionService::class)->createTransaction(
            shift: $shift,
            ticketLines: [[
                'event_id' => $this->event->id,
                'time_slot_id' => $this->slot->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 1,
            ]],
            productLines: [],
            paymentLines: [['method' => 'cash', 'amount' => 10]],
        );
    }

    public function test_opening_a_second_shift_on_the_same_open_terminal_is_rejected(): void
    {
        app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);

        $this->expectException(Exception::class);

        app(PosShiftService::class)->open($this->terminal, User::factory()->create(), 50);
    }

    // The slot has 5 seats. A first POS sale takes 4, leaving 1 — a second
    // POS sale for 2 more must be rejected, proving both sales draw from the
    // same live-computed pool (TicketType::getActiveSoldQuantity()) rather
    // than each channel tracking its own separate count.
    public function test_two_pos_sales_share_the_same_capacity_pool(): void
    {
        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);
        $service = app(PosTransactionService::class);

        $service->createTransaction(
            shift: $shift,
            ticketLines: [[
                'event_id' => $this->event->id,
                'time_slot_id' => $this->slot->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 4,
            ]],
            productLines: [],
            paymentLines: [['method' => 'cash', 'amount' => 40]],
        );

        $this->assertSame(1, $this->ticketType->fresh()->getRemainingQuantity());

        $this->expectException(Exception::class);

        try {
            $service->createTransaction(
                shift: $shift,
                ticketLines: [[
                    'event_id' => $this->event->id,
                    'time_slot_id' => $this->slot->id,
                    'ticket_type_id' => $this->ticketType->id,
                    'quantity' => 2, // only 1 left
                ]],
                productLines: [],
                paymentLines: [['method' => 'cash', 'amount' => 20]],
            );
        } finally {
            $this->assertSame(4, BookingAttendee::count());
            $this->assertSame(1, $this->ticketType->fresh()->getRemainingQuantity());
        }
    }

    public function test_refund_releases_ticket_capacity_and_restocks_merchandise(): void
    {
        $product = PosProduct::create(['name' => 'Water Bottle', 'sku' => 'WB-3', 'price' => 1, 'stock_quantity' => 10]);
        $shift = app(PosShiftService::class)->open($this->terminal, $this->cashier, 50);
        $service = app(PosTransactionService::class);

        $transaction = $service->createTransaction(
            shift: $shift,
            ticketLines: [[
                'event_id' => $this->event->id,
                'time_slot_id' => $this->slot->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 3,
            ]],
            productLines: [['product_id' => $product->id, 'quantity' => 2]],
            paymentLines: [['method' => 'cash', 'amount' => 32.10]],
        );

        $this->assertSame(2, $this->ticketType->fresh()->getRemainingQuantity());
        $this->assertSame(8, $product->fresh()->stock_quantity);

        $refunded = $service->refundTransaction($transaction, 'Customer changed plans');

        $this->assertSame('refunded', $refunded->status);
        $this->assertStringContainsString('Customer changed plans', $refunded->notes);
        $this->assertSame(5, $this->ticketType->fresh()->getRemainingQuantity()); // released back to full
        $this->assertSame(10, $product->fresh()->stock_quantity); // restocked
        $this->assertSame('refunded', BookingAttendee::first()->booking->fresh()->status);

        // Refunding twice should be rejected, not double-release capacity.
        $this->expectException(Exception::class);
        $service->refundTransaction($refunded, 'again');
    }
}

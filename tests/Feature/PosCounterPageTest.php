<?php

namespace Tests\Feature;

use App\Filament\Pages\PosCounter;
use App\Models\Event;
use App\Models\PosProduct;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
use App\Models\TicketType;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosCounterPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
    }

    public function test_full_sale_flow_through_the_page(): void
    {
        $event = Event::create([
            'title' => ['en' => 'Farm Day', 'ar' => 'يوم المزرعة'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => 'farm-day',
            'location' => ['en' => 'Venue', 'ar' => 'مكان'],
            'organizer' => 'Razat Farm',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'published',
            'max_attendees' => 1000,
        ]);

        $slot = TimeSlot::create([
            'event_id' => $event->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'max_attendees' => 20,
            'is_active' => true,
        ]);

        $ticketType = TicketType::create([
            'event_id' => $event->id,
            'name' => ['en' => 'Adult', 'ar' => 'بالغ'],
            'description' => ['en' => '', 'ar' => ''],
            'price' => 5,
            'quantity_available' => 20,
            'is_active' => true,
        ]);

        $product = PosProduct::create([
            'name' => 'Farm Honey Jar',
            'sku' => 'HJ-1',
            'price' => 3,
            'tax_rate' => 5,
            'stock_quantity' => 10,
        ]);

        PosTerminal::create(['name' => 'Gate 1', 'is_active' => true]);

        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        $this->actingAs($cashier);

        $test = Livewire::test(PosCounter::class);

        // No shift yet — opening a sale should be blocked until a shift opens.
        $test->set('openingFloat', 20)->call('openShift');

        $test->set('selectedEventId', $event->id)
            ->set('selectedTimeSlotId', $slot->id)
            ->set('selectedTicketTypeId', $ticketType->id)
            ->set('ticketQuantity', 2)
            ->call('addTicketToCart');

        $test->call('addProductToCart', $product->id);

        $totals = $test->instance()->cartTotals();
        // 2 tickets * 5 = 10, 1 honey jar * 3 = 3, tax 5% of 3 = 0.15 -> total 13.15
        $this->assertEqualsWithDelta(13.15, $totals['total'], 0.0001);

        $test->set('amountTendered', 20);
        $this->assertEqualsWithDelta(6.85, $test->instance()->changeDue(), 0.0001);

        $test->call('completeSale');
        $test->assertHasNoErrors();

        $this->assertSame(1, PosTransaction::count());
        $transaction = PosTransaction::first();
        $this->assertSame('completed', $transaction->status);
        $this->assertEqualsWithDelta(13.15, (float) $transaction->grand_total, 0.0001);
        $this->assertCount(3, $transaction->items); // 2 attendees + 1 product line

        $this->assertSame(18, $ticketType->fresh()->getRemainingQuantity());
        $this->assertSame(9, $product->fresh()->stock_quantity);

        // Cart resets after a completed sale.
        $test->assertSet('ticketCart', []);
        $test->assertSet('productCart', []);
    }
}

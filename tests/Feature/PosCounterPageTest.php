<?php

namespace Tests\Feature;

use App\Filament\Pages\PosCounter;
use App\Models\PosProduct;
use App\Models\PosTax;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
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

        $test->call('addProductToCart', $product->id);
        $test->call('addProductToCart', $product->id);

        $totals = $test->instance()->cartTotals();
        // 2 honey jars * 3 = 6, tax 5% of 6 = 0.3 -> total 6.3
        $this->assertEqualsWithDelta(6.3, $totals['total'], 0.0001);

        $test->call('openPaymentModal');
        $test->assertSet('showPaymentModal', true);

        // Split across cash + card, tendering more cash than owed — the
        // excess must come back as change rather than being recorded as revenue.
        $test->set('paymentRows.0.method', 'cash');
        $test->set('paymentRows.0.amount', 10);
        $test->call('addPaymentRow');
        $test->set('paymentRows.1.method', 'card');
        $test->set('paymentRows.1.amount', 0); // cash alone already covers the total; keep the card row idle

        // A card row with an amount but no reference must block finalizing.
        $test->set('paymentRows.1.amount', 1);
        $test->call('finalizePayment');
        $test->assertHasNoErrors(); // errors surface as notifications, not form errors
        $this->assertSame(0, PosTransaction::count());

        $test->set('paymentRows.1.amount', 0);
        $this->assertEqualsWithDelta(3.7, $test->instance()->paymentChangeReturn(), 0.0001);

        $test->call('finalizePayment');
        $test->assertHasNoErrors();

        $this->assertSame(1, PosTransaction::count());
        $transaction = PosTransaction::first();
        $this->assertSame('completed', $transaction->status);
        $this->assertEqualsWithDelta(6.3, (float) $transaction->grand_total, 0.0001);
        $this->assertCount(1, $transaction->items);

        // Only the amount actually owed was recorded — the tendered excess was change, not revenue.
        $this->assertSame(1, $transaction->payments()->count());
        $this->assertEqualsWithDelta(6.3, (float) $transaction->payments()->first()->amount, 0.0001);

        $this->assertSame(8, $product->fresh()->stock_quantity);

        // Cart resets after a completed sale.
        $test->assertSet('productCart', []);
        $test->assertSet('showPaymentModal', false);
    }

    public function test_payment_modal_discount_tax_and_shipping_submodals_affect_the_total(): void
    {
        $product = PosProduct::create([
            'name' => 'Farm Honey Jar',
            'sku' => 'HJ-1',
            'price' => 100,
            'tax_rate' => 0,
            'stock_quantity' => 10,
        ]);

        $tax = PosTax::create(['name' => 'City Tax', 'rate' => 10, 'is_active' => true]);
        $deliveryPerson = User::factory()->create(['name' => 'Delivery Dan']);

        PosTerminal::create(['name' => 'Gate 1', 'is_active' => true]);

        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        $this->actingAs($cashier);

        $test = Livewire::test(PosCounter::class);
        $test->set('openingFloat', 0)->call('openShift');
        $test->call('addProductToCart', $product->id);
        $test->call('openPaymentModal');

        // Ad-hoc discount: 10% off a 100 subtotal.
        $test->call('openDiscountModal');
        $test->set('adHocDiscountType', 'percentage');
        $test->set('adHocDiscountValue', 10);
        $test->call('saveDiscountModal');
        $test->assertSet('showDiscountModal', false);

        // Order tax: 10% of the 100 subtotal.
        $test->call('openOrderTaxModal');
        $test->set('orderTaxId', $tax->id);
        $test->call('saveOrderTax');

        // Shipping: flat charge + metadata.
        $test->call('openShippingModal');
        $test->set('shippingDetails', 'Handle with care');
        $test->set('shippingCharges', 15);
        $test->set('shippingStatus', 'pending');
        $test->set('deliveredTo', 'Front desk');
        $test->set('deliveryPersonId', $deliveryPerson->id);
        $test->call('saveShippingModal');
        $test->assertHasNoErrors();

        // 100 - 10 (discount) + 0 (item tax) + 10 (order tax) + 15 (shipping) = 115
        $totals = $test->instance()->cartTotals();
        $this->assertEqualsWithDelta(115.0, $totals['total'], 0.0001);

        $test->set('paymentRows.0.amount', 115);
        $test->call('finalizePayment');
        $test->assertHasNoErrors();

        $transaction = PosTransaction::first();
        $this->assertNotNull($transaction);
        $this->assertEqualsWithDelta(115.0, (float) $transaction->grand_total, 0.0001);
        $this->assertEqualsWithDelta(10.0, (float) $transaction->discount_total, 0.0001);
        $this->assertSame('percentage', $transaction->ad_hoc_discount_type);
        $this->assertSame($tax->id, $transaction->order_tax_id);
        $this->assertEqualsWithDelta(10.0, (float) $transaction->order_tax_total, 0.0001);
        $this->assertEqualsWithDelta(15.0, (float) $transaction->shipping_charges, 0.0001);
        $this->assertSame('Handle with care', $transaction->shipping_details);
        $this->assertSame('pending', $transaction->shipping_status);
        $this->assertSame('Front desk', $transaction->delivered_to);
        $this->assertSame($deliveryPerson->id, $transaction->delivery_person_id);
    }

    public function test_discount_submodal_cancel_reverts_pending_changes(): void
    {
        $product = PosProduct::create([
            'name' => 'Farm Honey Jar',
            'sku' => 'HJ-1',
            'price' => 100,
            'tax_rate' => 0,
            'stock_quantity' => 10,
        ]);

        PosTerminal::create(['name' => 'Gate 1', 'is_active' => true]);

        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        $this->actingAs($cashier);

        $test = Livewire::test(PosCounter::class);
        $test->set('openingFloat', 0)->call('openShift');
        $test->call('addProductToCart', $product->id);
        $test->call('openPaymentModal');

        $test->call('openDiscountModal');
        $test->set('adHocDiscountValue', 25);
        $test->call('cancelDiscountModal');

        $test->assertSet('adHocDiscountValue', 0.0);
        $totals = $test->instance()->cartTotals();
        $this->assertEqualsWithDelta(100.0, $totals['total'], 0.0001);
    }

    public function test_card_payment_requires_a_transaction_reference(): void
    {
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
        $test->set('openingFloat', 0)->call('openShift');
        $test->call('addProductToCart', $product->id);
        $test->call('openPaymentModal');

        $test->set('paymentRows.0.method', 'card');
        $test->set('paymentRows.0.card_holder_name', 'Jane Doe');
        // No reference/transaction number set — should be rejected.
        $test->call('finalizePayment');

        $this->assertSame(0, PosTransaction::count());

        $test->set('paymentRows.0.reference', 'TXN-001');
        $test->call('finalizePayment');

        $this->assertSame(1, PosTransaction::count());
        $payment = PosTransaction::first()->payments()->first();
        $this->assertSame('card', $payment->method);
        $this->assertSame('TXN-001', $payment->reference);
        $this->assertSame('Jane Doe', $payment->card_holder_name);
    }

    public function test_recent_transactions_modal_can_edit_print_and_void(): void
    {
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
        $test->set('openingFloat', 0)->call('openShift');
        $test->call('addProductToCart', $product->id);
        $test->call('openPaymentModal');
        $test->set('paymentRows.0.amount', 3.15);
        $test->call('finalizePayment');

        $transaction = PosTransaction::first();
        $this->assertNotNull($transaction);

        $test->call('openTransactionsModal');
        $test->assertSet('showTransactionsModal', true);

        $test->call('reprintTransaction', $transaction->id);
        $test->assertHasNoErrors();

        $test->call('openEditTransaction', $transaction->id);
        $test->set('editSellNote', 'Customer requested a gift receipt');
        $test->call('saveTransactionEdit');

        $this->assertSame('Customer requested a gift receipt', $transaction->fresh()->notes);

        $test->call('confirmVoidTransaction', $transaction->id);
        $test->assertSet('confirmVoidTransactionId', $transaction->id);

        $test->call('voidRecentTransaction');
        $test->assertHasNoErrors();

        $this->assertSame('voided', $transaction->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }
}

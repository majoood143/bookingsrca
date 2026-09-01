<?php

namespace Tests\Feature;

use App\Filament\Pages\PosSalesReport;
use App\Filament\Pages\PosTransactionsReport;
use App\Filament\Resources\PosBrandResource\Pages\ListPosBrands;
use App\Filament\Resources\PosOrderResource\Pages\ListPosOrders;
use App\Filament\Resources\PosProductResource\Pages\ListPosProducts;
use App\Models\PosBrand;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosShopTest extends TestCase
{
    use RefreshDatabase;

    private function publishedProduct(array $overrides = []): PosProduct
    {
        return PosProduct::create(array_merge([
            'name' => 'Farm Honey Jar',
            'slug' => 'farm-honey-jar',
            'sku' => 'HJ-1',
            'price' => 3,
            'tax_rate' => 5,
            'stock_quantity' => 10,
            'is_active' => true,
            'is_published_online' => true,
        ], $overrides));
    }

    public function test_catalog_page_renders_published_products(): void
    {
        $this->publishedProduct();

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Farm Honey Jar');
    }

    public function test_catalog_hides_unpublished_products(): void
    {
        $this->publishedProduct(['is_published_online' => false, 'slug' => 'hidden']);

        $this->get(route('shop.index'))->assertOk()->assertDontSee('Farm Honey Jar');
    }

    public function test_product_detail_page_renders_and_add_to_cart_works(): void
    {
        $product = $this->publishedProduct();

        $this->get(route('shop.product.show', $product->slug))
            ->assertOk()
            ->assertSee('Farm Honey Jar');

        Livewire::test(\App\Livewire\Shop\ProductDetail::class, ['product' => $product])
            ->call('addToCart')
            ->assertHasNoErrors();

        $this->assertSame(1, \App\Support\ShopCart::count());
    }

    public function test_checkout_page_renders(): void
    {
        $this->get(route('shop.checkout'))->assertOk();
    }

    public function test_order_marking_paid_records_payment_and_decrements_stock(): void
    {
        $product = $this->publishedProduct(['stock_quantity' => 5]);
        $customer = PosCustomer::create(['type' => 'individual', 'name' => 'Buyer', 'email' => 'buyer@example.com', 'mobile' => '99999999']);

        $order = PosOrder::create([
            'order_number' => PosOrder::nextOrderNumber(),
            'customer_id' => $customer->id,
            'contact_name' => 'Buyer',
            'contact_email' => 'buyer@example.com',
            'contact_mobile' => '99999999',
            'subtotal' => $product->price,
            'tax_total' => 0,
            'grand_total' => $product->price,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => $product->price,
            'line_total' => $product->price * 2,
        ]);

        $order->markPaid('thawani', 'REF-1');

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('preparing', $order->fresh()->fulfillment_status);
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame(3, $product->fresh()->stock_quantity);
    }

    public function test_admin_can_view_pos_shop_resources(): void
    {
        // Mirrors what `php artisan shield:generate` does in a real deployment:
        // it creates the permission records the resource policies check,
        // which this isolated test database doesn't have without it.
        $role = Role::findOrCreate('super_admin', 'web');
        foreach (['ViewAny:PosBrand', 'ViewAny:PosProduct', 'ViewAny:PosOrder'] as $permission) {
            $role->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate($permission, 'web'));
        }

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        $this->actingAs($admin);

        PosBrand::create(['name' => 'Acme', 'slug' => 'acme']);
        $this->publishedProduct();

        Livewire::test(ListPosBrands::class)->assertOk();
        Livewire::test(ListPosProducts::class)->assertOk();
        Livewire::test(ListPosOrders::class)->assertOk();
    }

    public function test_admin_can_view_sales_and_transactions_reports(): void
    {
        $role = Role::findOrCreate('super_admin', 'web');
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        $this->actingAs($admin);

        $product = $this->publishedProduct(['stock_quantity' => 10]);
        $customer = PosCustomer::create(['type' => 'individual', 'name' => 'Buyer', 'email' => 'buyer@example.com', 'mobile' => '99999999']);

        $order = PosOrder::create([
            'order_number' => PosOrder::nextOrderNumber(),
            'customer_id' => $customer->id,
            'contact_name' => 'Buyer',
            'contact_email' => 'buyer@example.com',
            'contact_mobile' => '99999999',
            'subtotal' => $product->price,
            'tax_total' => 0,
            'grand_total' => $product->price,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => $product->price,
            'line_total' => $product->price,
        ]);
        $order->markPaid('thawani', 'REF-2');

        Livewire::test(PosSalesReport::class)->assertOk();
        Livewire::test(PosTransactionsReport::class)->assertOk();

        $salesReport = (new PosSalesReport())->getReportData();
        $this->assertSame(1, $salesReport['orderCount']);
        $this->assertEquals((float) $product->price, $salesReport['totalRevenue']);

        $transactionsReport = (new PosTransactionsReport())->getReportData();
        $this->assertSame(1, $transactionsReport['transactionCount']);
    }
}

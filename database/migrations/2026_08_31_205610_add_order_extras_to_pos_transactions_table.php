<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            // Manual, typed-in-the-payment-modal discount that overrides the
            // code-based one (discount_id) when set — see PosCounter::cartTotals().
            $table->enum('ad_hoc_discount_type', ['percentage', 'fixed_amount'])->nullable()->after('discount_id');
            $table->decimal('ad_hoc_discount_value', 12, 3)->nullable()->after('ad_hoc_discount_type');

            $table->foreignId('order_tax_id')->nullable()->after('ad_hoc_discount_value')->constrained('pos_taxes')->nullOnDelete();
            $table->decimal('order_tax_total', 12, 3)->default(0)->after('order_tax_id');

            $table->text('shipping_details')->nullable()->after('order_tax_total');
            $table->text('shipping_address')->nullable()->after('shipping_details');
            $table->decimal('shipping_charges', 12, 3)->default(0)->after('shipping_address');
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered', 'cancelled'])->nullable()->after('shipping_charges');
            $table->string('delivered_to')->nullable()->after('shipping_status');
            $table->foreignId('delivery_person_id')->nullable()->after('delivered_to')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_person_id');
            $table->dropColumn([
                'shipping_status', 'delivered_to',
            ]);
            $table->dropColumn(['shipping_details', 'shipping_address', 'shipping_charges']);
            $table->dropConstrainedForeignId('order_tax_id');
            $table->dropColumn(['order_tax_total', 'ad_hoc_discount_type', 'ad_hoc_discount_value']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('pos_customers')->restrictOnDelete();

            // Payment lifecycle, mirrors the outcome states a gateway callback can settle into.
            $table->enum('status', ['pending_payment', 'paid', 'failed', 'cancelled'])->default('pending_payment');
            // Fulfilment lifecycle, separate from payment status so "paid" orders can be
            // tracked through preparation without conflating the two concerns.
            $table->enum('fulfillment_status', ['pending', 'preparing', 'ready', 'completed', 'cancelled'])->default('pending');

            // Snapshot of the guest's details at checkout time, independent of the
            // linked PosCustomer record which may be edited later.
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_mobile');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();

            $table->decimal('subtotal', 12, 3);
            $table->decimal('discount_total', 12, 3)->default(0);
            $table->decimal('tax_total', 12, 3)->default(0);
            $table->decimal('grand_total', 12, 3);

            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('customer_note')->nullable();
            $table->text('staff_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};

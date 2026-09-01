<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('pos_products')->nullOnDelete();
            // Snapshot so a past order's line items still read correctly even if the
            // product is later renamed or deleted.
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 3);
            $table->decimal('line_total', 12, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
    }
};

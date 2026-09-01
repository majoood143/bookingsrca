<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('pos_products')->cascadeOnDelete();
            // Proof of a verified purchase: the review can only be created against an
            // order that actually contains this product and was paid.
            $table->foreignId('order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('pos_customers')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            // A customer reviews a given product at most once per order.
            $table->unique(['product_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_product_reviews');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Self-referencing: a row with no parent is a top-level category,
            // a row with a parent is that category's sub-category — one table
            // covers both instead of a separate "sub-category" concept.
            $table->foreignId('parent_id')->nullable()->constrained('pos_product_categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_product_categories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->json('name'); // Translatable {"en": "...", "ar": "..."}
            $table->json('description')->nullable(); // Translatable
            $table->string('color', 7)->default('#6366f1');
            $table->string('icon', 50)->default('heroicon-o-banknotes');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};

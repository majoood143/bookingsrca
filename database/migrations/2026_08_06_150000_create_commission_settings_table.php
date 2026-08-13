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
        Schema::create('commission_settings', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('event_id')->nullable()
                ->constrained('events')->cascadeOnDelete()
                ->comment('Event this commission rate applies to, null for the global/platform-wide rate');

            $table->json('name'); // Translatable
            $table->json('description')->nullable(); // Translatable

            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('commission_value', 10, 3);

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['event_id', 'is_active'], 'commission_settings_event_active_index');
            $table->index(['effective_from', 'effective_to'], 'commission_settings_effective_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_settings');
    }
};

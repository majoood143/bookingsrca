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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // Translatable
            $table->json('description'); // Translatable
            $table->string('slug')->unique();
            $table->json('location'); // Translatable
            $table->string('organizer');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurring_days')->nullable(); // ['monday', 'wednesday', 'friday']
            $table->string('image')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
            $table->integer('max_attendees')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

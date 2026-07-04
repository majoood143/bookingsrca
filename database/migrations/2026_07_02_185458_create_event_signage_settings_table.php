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
        Schema::create('event_signage_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('qr_code_image_path')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('meeting_point')->nullable();
            $table->json('welcome_message')->nullable();
            $table->unsignedInteger('early_arrival_minutes')->default(5);
            $table->unsignedInteger('gathering_alert_minutes')->default(5);
            $table->unsignedInteger('ready_threshold_minutes')->default(15);
            $table->unsignedInteger('soon_threshold_minutes')->default(60);
            $table->unsignedInteger('upcoming_trips_count')->default(4);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_signage_settings');
    }
};

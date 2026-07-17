<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_report_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->json('recipients');
            $table->boolean('is_enabled')->default(false);
            $table->unsignedTinyInteger('send_day')->default(1); // 0=Sunday..6=Saturday
            $table->time('send_time')->default('08:00:00');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_report_subscriptions');
    }
};

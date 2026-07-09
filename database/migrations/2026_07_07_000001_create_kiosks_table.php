<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete(); // null = customer picks the event on the kiosk
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('idle_timeout_seconds')->default(90);
            $table->json('enabled_payment_methods')->nullable(); // e.g. ["wallet", "pay_at_counter"]
            $table->json('receipt_footer_text')->nullable(); // translatable
            $table->boolean('reader_connected')->default(false);
            $table->timestamp('reader_last_seen_at')->nullable();
            $table->boolean('printer_connected')->default(false);
            $table->timestamp('printer_last_seen_at')->nullable();
            $table->string('app_version')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosks');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per WhatsApp or SMS message the app tried to send to a customer (one-time codes,
 * game winner vouchers, test messages), sent or failed, so delivery problems can be traced.
 * One-time code texts are never stored (message stays null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20);
            $table->string('type', 50);
            $table->string('recipient', 32)->index();
            $table->string('status', 20);
            $table->text('message')->nullable();
            $table->string('provider_reference')->nullable();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index(['channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};

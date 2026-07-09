<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosk_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kiosk_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kiosk_id')->nullable()->constrained()->nullOnDelete(); // null for counter top-ups
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete(); // set for payment/refund
            $table->string('type'); // topup | payment | refund | adjustment
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); // null for self-service kiosk payments
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_card_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('pos_transactions')->cascadeOnDelete();
            // Ticket lines point at BookingAttendee (one row per seat, sharing the
            // same capacity-locked Booking created via SeatAllocationService);
            // merchandise lines point at PosProduct. The two branches never touch
            // each other's stock (see §4.1 of the POS spec).
            $table->string('itemable_type');
            $table->unsignedBigInteger('itemable_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 3);
            $table->decimal('discount_amount', 12, 3)->default(0);
            $table->decimal('tax_amount', 12, 3)->default(0);
            $table->decimal('line_total', 12, 3);
            $table->timestamps();

            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transaction_items');
    }
};

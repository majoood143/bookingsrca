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
        Schema::table('booking_attendees', function (Blueprint $table) {
            //
            $table->foreignId('ticket_type_id')->nullable()->after('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('ticket_price', 10, 2)->default(0)->after('ticket_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_attendees', function (Blueprint $table) {
            $table->dropForeign(['ticket_type_id']);
            $table->dropColumn(['ticket_type_id', 'ticket_price']);
        });
    }
};

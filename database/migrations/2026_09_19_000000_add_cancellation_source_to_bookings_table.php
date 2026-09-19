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
        // Who/what cancelled the booking: customer | expired | gateway | admin | error.
        // Lets late-payment recovery tell a booking the system gave up on (safe to
        // revive when the gateway later reports it paid) apart from one an admin
        // cancelled on purpose.
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('cancellation_source', 20)->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('cancellation_source');
        });
    }
};

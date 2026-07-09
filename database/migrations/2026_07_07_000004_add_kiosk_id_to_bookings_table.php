<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // source gains a 'kiosk' value; payment_method gains 'kiosk_wallet' (both plain strings, no enum change needed)
            $table->foreignId('kiosk_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kiosk_id');
        });
    }
};

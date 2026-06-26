<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');          // thawani | cash | free
            $table->string('payment_status')->nullable()->default('pending')->after('payment_method'); // pending | paid | failed | cancelled
            $table->string('payment_session_id')->nullable()->after('payment_status'); // Thawani session_id
            $table->string('payment_reference')->nullable()->after('payment_session_id'); // Thawani payment_ref
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'payment_session_id', 'payment_reference']);
        });
    }
};

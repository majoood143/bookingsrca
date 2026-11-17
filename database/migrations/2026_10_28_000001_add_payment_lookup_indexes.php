<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Every gateway callback, webhook and expiry sweep looks a payable up by these.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('payment_session_id');
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->index('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['payment_session_id']);
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropIndex(['payment_reference']);
        });
    }
};

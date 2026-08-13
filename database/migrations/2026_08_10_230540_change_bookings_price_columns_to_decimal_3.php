<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('ticket_price', 10, 3)->change();
            $table->decimal('services_price', 10, 3)->default(0)->change();
            $table->decimal('total_price', 10, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('ticket_price', 10, 2)->change();
            $table->decimal('services_price', 10, 2)->default(0)->change();
            $table->decimal('total_price', 10, 2)->change();
        });
    }
};

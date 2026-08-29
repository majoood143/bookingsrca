<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY is MySQL-only; SQLite (test suite) needs a plain column-type change.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->enum('payment_method', ['cash', 'credit_debit', 'partial', 'thawani', 'nbo', 'ccavenue'])->change();
            });

            return;
        }

        DB::statement("ALTER TABLE booking_payments MODIFY payment_method ENUM('cash', 'credit_debit', 'partial', 'thawani', 'nbo', 'ccavenue') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('booking_payments', function (Blueprint $table) {
                $table->enum('payment_method', ['cash', 'credit_debit', 'partial'])->change();
            });

            return;
        }

        DB::statement("ALTER TABLE booking_payments MODIFY payment_method ENUM('cash', 'credit_debit', 'partial') NOT NULL");
    }
};

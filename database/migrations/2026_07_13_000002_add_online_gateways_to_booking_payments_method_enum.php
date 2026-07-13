<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE booking_payments MODIFY payment_method ENUM('cash', 'credit_debit', 'partial', 'thawani', 'nbo', 'ccavenue') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE booking_payments MODIFY payment_method ENUM('cash', 'credit_debit', 'partial') NOT NULL");
    }
};

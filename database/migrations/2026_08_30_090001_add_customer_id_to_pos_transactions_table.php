<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('discount_id')
                ->constrained('pos_customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};

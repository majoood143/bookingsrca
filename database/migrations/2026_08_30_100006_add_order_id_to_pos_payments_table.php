<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Lets a settled online-order payment be recorded in the same pos_payments
    // table as counter payments, so admins see one consistent payment record
    // shape regardless of channel. Exactly one of transaction_id/order_id is
    // set per row — enforced in the model layer, since portable check
    // constraints across the app's supported DB drivers aren't worth the cost
    // for a single either/or pair.
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->change();
            $table->foreignId('order_id')->nullable()->after('transaction_id')
                ->constrained('pos_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->foreignId('transaction_id')->nullable(false)->change();
        });
    }
};

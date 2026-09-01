<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Separate from `notes` (the customer-facing sell note, also used for the
    // void/refund audit trail) so a cashier can leave an internal-only note
    // without it ever reaching a printed receipt.
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->text('staff_note')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropColumn('staff_note');
        });
    }
};

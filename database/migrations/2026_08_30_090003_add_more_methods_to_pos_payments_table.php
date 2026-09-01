<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Widened from a DB-level ENUM to a plain string: the payment-method list
    // now grows at the app layer (see PosCounter's payment modal) rather than
    // needing a migration every time a new method is added, and a driver-agnostic
    // add/copy/drop/rename avoids MySQL-only `MODIFY` syntax that breaks on sqlite.
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->string('method_new', 30)->nullable()->after('method');
        });

        DB::table('pos_payments')->update(['method_new' => DB::raw('method')]);

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropColumn('method');
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->renameColumn('method_new', 'method');
        });
    }

    public function down(): void
    {
        // Not safely reversible once values outside the original enum exist
        // (cash, card, mobile_wallet, split) — left as a plain string.
    }
};

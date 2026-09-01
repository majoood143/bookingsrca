<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Defensive re-run of 090003_add_more_methods_to_pos_payments_table: on at
    // least one environment that migration is recorded as run but the column
    // is still the old ENUM (rejects 'thawani'/'nbo'/'ccavenue' now used by
    // online shop payments), so redo the same add/copy/drop/rename here
    // regardless of the column's current type — a no-op in shape either way.
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->string('method_v2', 30)->nullable()->after('method');
        });

        DB::table('pos_payments')->update(['method_v2' => DB::raw('method')]);

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropColumn('method');
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->renameColumn('method_v2', 'method');
        });
    }

    public function down(): void
    {
        // Not safely reversible once values outside the original enum exist.
    }
};

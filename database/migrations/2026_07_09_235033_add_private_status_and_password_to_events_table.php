<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'cancelled', 'private'])
                ->default('draft')
                ->change();

            $table->string('password')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        DB::table('events')->where('status', 'private')->update(['status' => 'draft']);

        Schema::table('events', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'cancelled'])
                ->default('draft')
                ->change();
        });
    }
};

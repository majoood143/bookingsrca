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
        Schema::table('time_slots', function (Blueprint $table) {
            $table->renameColumn('label', 'label_old');
        });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->json('label')->nullable()->after('end_time');
        });

        DB::table('time_slots')
            ->whereNotNull('label_old')
            ->where('label_old', '!=', '')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('time_slots')->where('id', $row->id)->update([
                    'label' => json_encode(['en' => $row->label_old, 'ar' => $row->label_old]),
                ]);
            });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn('label_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->renameColumn('label', 'label_json_old');
        });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->string('label')->nullable()->after('end_time');
        });

        DB::table('time_slots')
            ->whereNotNull('label_json_old')
            ->orderBy('id')
            ->each(function ($row) {
                $decoded = json_decode($row->label_json_old, true);

                DB::table('time_slots')->where('id', $row->id)->update([
                    'label' => $decoded['en'] ?? null,
                ]);
            });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn('label_json_old');
        });
    }
};

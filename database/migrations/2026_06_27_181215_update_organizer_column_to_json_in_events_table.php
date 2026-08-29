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
        // MODIFY and JSON_OBJECT() are MySQL-only. The test suite runs
        // migrations from scratch against SQLite with no legacy data to
        // convert, so there it's just a plain column-type change.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('events', function (Blueprint $table) {
                $table->json('organizer')->nullable(false)->change();
            });

            return;
        }

        // Build a JSON object from the existing plain-text value while the
        // column is still varchar, then promote the column to json.
        DB::statement("UPDATE events SET organizer = JSON_OBJECT('en', organizer, 'ar', organizer)");
        DB::statement('ALTER TABLE events MODIFY organizer JSON NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('events', function (Blueprint $table) {
                $table->string('organizer')->nullable(false)->change();
            });

            return;
        }

        // Relax to text first so we can read the JSON without column-type
        // validation getting in the way, then collapse back to a plain string.
        DB::statement('ALTER TABLE events MODIFY organizer TEXT NOT NULL');
        DB::statement("UPDATE events SET organizer = JSON_UNQUOTE(JSON_EXTRACT(organizer, '$.en'))");
        DB::statement('ALTER TABLE events MODIFY organizer VARCHAR(255) NOT NULL');
    }
};

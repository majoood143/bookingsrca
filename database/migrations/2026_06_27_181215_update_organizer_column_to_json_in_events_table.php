<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
        // Relax to text first so we can read the JSON without column-type
        // validation getting in the way, then collapse back to a plain string.
        DB::statement('ALTER TABLE events MODIFY organizer TEXT NOT NULL');
        DB::statement("UPDATE events SET organizer = JSON_UNQUOTE(JSON_EXTRACT(organizer, '$.en'))");
        DB::statement('ALTER TABLE events MODIFY organizer VARCHAR(255) NOT NULL');
    }
};

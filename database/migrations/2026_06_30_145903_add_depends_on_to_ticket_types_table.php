<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreignId('depends_on_ticket_type_id')
                ->nullable()
                ->after('is_active')
                ->constrained('ticket_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropForeign(['depends_on_ticket_type_id']);
            $table->dropColumn('depends_on_ticket_type_id');
        });
    }
};

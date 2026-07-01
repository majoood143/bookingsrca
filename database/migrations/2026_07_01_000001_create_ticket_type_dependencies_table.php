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
        Schema::create('ticket_type_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')
                ->constrained('ticket_types')
                ->cascadeOnDelete();
            $table->foreignId('depends_on_ticket_type_id')
                ->constrained('ticket_types')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_type_id', 'depends_on_ticket_type_id'], 'ticket_type_dependencies_unique');
        });

        DB::table('ticket_types')
            ->whereNotNull('depends_on_ticket_type_id')
            ->select('id', 'depends_on_ticket_type_id')
            ->orderBy('id')
            ->each(function ($ticketType) {
                DB::table('ticket_type_dependencies')->insert([
                    'ticket_type_id' => $ticketType->id,
                    'depends_on_ticket_type_id' => $ticketType->depends_on_ticket_type_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropForeign(['depends_on_ticket_type_id']);
            $table->dropColumn('depends_on_ticket_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreignId('depends_on_ticket_type_id')
                ->nullable()
                ->after('is_active')
                ->constrained('ticket_types')
                ->nullOnDelete();
        });

        DB::table('ticket_type_dependencies')
            ->orderBy('ticket_type_id')
            ->each(function ($dependency) {
                DB::table('ticket_types')
                    ->where('id', $dependency->ticket_type_id)
                    ->update(['depends_on_ticket_type_id' => $dependency->depends_on_ticket_type_id]);
            });

        Schema::dropIfExists('ticket_type_dependencies');
    }
};

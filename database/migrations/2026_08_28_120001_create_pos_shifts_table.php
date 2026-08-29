<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('opening_float', 12, 3);
            $table->decimal('closing_float', 12, 3)->nullable();
            $table->decimal('expected_cash', 12, 3)->nullable();
            $table->decimal('counted_cash', 12, 3)->nullable();
            $table->decimal('variance', 12, 3)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // A terminal must have at most one open shift at a time — enforced
            // at the model/service layer (see PosShiftService), this index just
            // makes the "find the open shift for a terminal" lookup fast.
            $table->index(['terminal_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_shifts');
    }
};

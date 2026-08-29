<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('pos_shifts')->cascadeOnDelete();
            $table->foreignId('terminal_id')->constrained('pos_terminals')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained('pos_discounts')->nullOnDelete();
            // Sequential per terminal (e.g. "T1-000042") for receipt reprints/reconciliation.
            $table->string('transaction_number')->unique();
            $table->decimal('subtotal', 12, 3);
            $table->decimal('discount_total', 12, 3)->default(0);
            $table->decimal('tax_total', 12, 3)->default(0);
            $table->decimal('grand_total', 12, 3);
            $table->enum('status', ['completed', 'refunded', 'partially_refunded', 'voided'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['terminal_id', 'transaction_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};

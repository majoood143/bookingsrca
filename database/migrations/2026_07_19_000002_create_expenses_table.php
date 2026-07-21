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
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();

            $table->string('expense_number', 20)->unique()
                ->comment('Unique expense reference (e.g., EXP-2026-00001)');

            $table->foreignId('event_id')->nullable()
                ->constrained('events')->nullOnDelete()
                ->comment('Event this expense relates to, null for general expenses');

            $table->foreignId('booking_id')->nullable()
                ->constrained('bookings')->nullOnDelete()
                ->comment('Specific booking this expense is linked to');

            $table->foreignId('category_id')->nullable()
                ->constrained('expense_categories')->nullOnDelete();

            $table->enum('expense_type', ['event', 'operational', 'recurring', 'one_time'])
                ->default('operational');

            $table->json('title'); // Translatable
            $table->json('description')->nullable(); // Translatable

            $table->decimal('amount', 12, 3);
            $table->string('currency', 3)->default('OMR');
            $table->decimal('tax_amount', 12, 3)->default(0.000);
            $table->decimal('total_amount', 12, 3)->storedAs('amount + tax_amount');

            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'cheque', 'other'])
                ->default('cash');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'cancelled'])
                ->default('paid');
            $table->string('payment_reference', 100)->nullable();

            $table->date('expense_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->string('vendor_name', 255)->nullable();
            $table->string('vendor_phone', 20)->nullable();
            $table->string('vendor_email', 255)->nullable();

            $table->json('attachments')->nullable();

            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])
                ->nullable();
            $table->date('recurring_start_date')->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->unsignedInteger('recurring_count')->default(0);
            $table->foreignId('parent_expense_id')->nullable()
                ->constrained('expenses')->nullOnDelete();

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'archived'])
                ->default('approved');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'expense_date'], 'expenses_event_date_index');
            $table->index(['booking_id'], 'expenses_booking_index');
            $table->index(['category_id'], 'expenses_category_index');
            $table->index(['expense_type'], 'expenses_type_index');
            $table->index(['payment_status'], 'expenses_payment_status_index');
            $table->index(['status'], 'expenses_status_index');
            $table->index(['expense_date'], 'expenses_date_index');
            $table->index(['is_recurring', 'recurring_frequency'], 'expenses_recurring_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

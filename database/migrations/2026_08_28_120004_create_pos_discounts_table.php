<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_discounts', function (Blueprint $table) {
            $table->id();
            // Null code = manager-discretion discount, not customer-facing.
            $table->string('code')->nullable()->unique();
            $table->enum('type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 12, 3);
            $table->enum('applies_to', ['tickets', 'merchandise', 'all'])->default('all');
            $table->boolean('requires_approval')->default(false);
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_discounts');
    }
};

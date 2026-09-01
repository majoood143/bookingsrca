<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['individual', 'business'])->default('individual');
            $table->string('name');
            $table->string('business_name')->nullable();
            // Nullable at the DB level so the single seeded walk-in row (no real
            // phone) can exist — the counter form still requires it for anyone else.
            $table->string('mobile')->nullable();
            $table->string('alternate_contact')->nullable();
            $table->string('email')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            // Marks the single default counter customer used when no real
            // contact is picked — never created twice, see PosCustomer::walkIn().
            $table->boolean('is_walk_in')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_customers');
    }
};

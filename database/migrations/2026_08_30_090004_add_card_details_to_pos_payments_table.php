<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Deliberately narrow: only the non-sensitive fields a cashier needs for
    // reconciling against the physical card terminal's own receipt. The full
    // PAN, expiry, and CVV are never captured here — Livewire ships public
    // component state to the browser on every render, and this app has no
    // PCI-DSS-scoped storage for that data, so persisting it would be an
    // active security liability rather than a convenience.
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->string('card_holder_name')->nullable()->after('reference');
            $table->string('card_last4', 4)->nullable()->after('card_holder_name');
            $table->string('card_type', 30)->nullable()->after('card_last4');
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropColumn(['card_holder_name', 'card_last4', 'card_type']);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a promo code work only for one mobile number (a game winner's voucher) and cap a
 * percentage discount at an amount. `source` tells generated codes apart ("game").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->string('restricted_mobile', 20)->nullable()->after('event_id')->index();
            $table->decimal('max_discount_amount', 10, 3)->nullable()->after('discount_value');
            $table->string('source', 30)->nullable()->after('restricted_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropIndex(['restricted_mobile']);
            $table->dropColumn(['restricted_mobile', 'max_discount_amount', 'source']);
        });
    }
};

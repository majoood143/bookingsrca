<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosk_cards', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // NFC tag UID read by the ACR122U
            $table->string('holder_name')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('status')->default('active'); // active | blocked
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_cards');
    }
};

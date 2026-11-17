<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        // A zone is one compiled MindAR target file: the camera only matches items of the zone it scans.
        Schema::create('ar_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ar_destination_id')->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('target_order')->nullable(); // item ids, index = MindAR targetIndex
            $table->timestamp('targets_compiled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ar_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ar_zone_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_items');
        Schema::dropIfExists('ar_zones');
        Schema::dropIfExists('ar_destinations');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Games module: admin-run campaigns (period, rules, prizes, branding), the players who
 * verified their mobile, every attempt and shot the server decided, and the vouchers awarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('game_type', 30)->default('penalty');
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft | published | archived
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            // Rules
            $table->unsignedTinyInteger('shots_per_attempt')->default(5);
            $table->unsignedSmallInteger('attempts_per_day')->nullable()->default(3);
            $table->unsignedSmallInteger('attempts_total')->nullable();
            $table->unsignedTinyInteger('keeper_skill')->default(50); // 0-100
            $table->unsignedTinyInteger('risk_threshold')->default(60); // 0-100, attempts at/above wait for review

            // Leaderboard
            $table->boolean('leaderboard_public')->default(true);
            $table->unsignedSmallInteger('leaderboard_size')->default(10);
            $table->dateTime('leaderboard_freeze_at')->nullable();

            // Branding (empty = the app's own branding from General Settings)
            $table->string('primary_color', 9)->nullable();
            $table->string('secondary_color', 9)->nullable();
            $table->string('accent_color', 9)->nullable();
            $table->string('kit_color', 9)->nullable();
            $table->string('keeper_kit_color', 9)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('sponsor_logo_path')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->json('sponsor_name')->nullable();
            $table->json('headline')->nullable();
            $table->json('tagline')->nullable();
            $table->json('board_text')->nullable();
            $table->json('prize_text')->nullable();
            $table->json('terms')->nullable();
            $table->json('share_text')->nullable();

            $table->dateTime('awarded_at')->nullable();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at', 'ends_at']);
        });

        Schema::create('game_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->json('title')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 3);
            $table->decimal('max_discount_amount', 10, 3)->nullable();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->unsignedSmallInteger('valid_days')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['game_campaign_id', 'rank']);
        });

        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20)->unique();
            $table->string('name', 80);
            $table->string('locale', 5)->default('ar');
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('terms_accepted_at')->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->dateTime('blocked_at')->nullable();
            $table->string('blocked_reason')->nullable();
            $table->string('first_ip', 45)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('last_device_hash', 64)->nullable()->index();
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('game_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('game_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('started'); // started | completed | abandoned
            $table->unsignedInteger('seed');
            $table->unsignedTinyInteger('shots_taken')->default(0);
            $table->unsignedTinyInteger('goals')->default(0);
            $table->dateTime('started_at', 3);
            $table->dateTime('last_shot_at', 3)->nullable();
            $table->dateTime('finished_at', 3)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedSmallInteger('risk_score')->default(0);
            $table->json('flags')->nullable();
            $table->string('review_status', 20)->nullable(); // flagged | cleared | invalidated
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('review_note')->nullable();
            $table->string('ip', 45)->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('device_hash', 64)->nullable()->index();
            $table->timestamps();

            $table->index(['game_campaign_id', 'status', 'review_status']);
            $table->index(['game_player_id', 'game_campaign_id', 'started_at']);
        });

        Schema::create('game_shots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_attempt_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('n');
            $table->json('input');
            $table->decimal('aim_x', 6, 3);
            $table->decimal('aim_y', 6, 3);
            $table->decimal('power', 4, 3);
            $table->json('keeper');
            $table->string('outcome', 10); // goal | save | post | wide | over
            $table->unsignedInteger('gap_ms')->nullable();
            $table->timestamps();

            $table->unique(['game_attempt_id', 'n']);
        });

        Schema::create('game_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('game_prize_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('awarded'); // awarded | notified | void
            $table->dateTime('notified_at')->nullable();
            $table->string('notify_channel', 20)->nullable();
            $table->string('notify_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_awards');
        Schema::dropIfExists('game_shots');
        Schema::dropIfExists('game_attempts');
        Schema::dropIfExists('game_players');
        Schema::dropIfExists('game_prizes');
        Schema::dropIfExists('game_campaigns');
    }
};

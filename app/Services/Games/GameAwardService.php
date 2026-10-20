<?php

namespace App\Services\Games;

use App\Models\BookingSetting;
use App\Models\GameAttempt;
use App\Models\GameAward;
use App\Models\GameCampaign;
use App\Models\GamePrize;
use App\Models\PromoCode;
use App\Models\User;
use App\Services\Messaging\CustomerMessenger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Closes a campaign: the players at the prize ranks each get a single-use promo code bound to
 * their mobile (so a shared screenshot is worthless), then a WhatsApp (or SMS) with the code.
 * Ranking uses the live board, not a frozen public one.
 */
class GameAwardService
{
    public function __construct(
        private readonly LeaderboardService $leaderboard,
        private readonly CustomerMessenger $messenger,
    ) {}

    /**
     * Why the campaign cannot be finalized yet, or null when it can.
     */
    public function blocker(GameCampaign $campaign): ?string
    {
        if ($campaign->isAwarded()) {
            return __('games.award.already_awarded');
        }

        if (! $campaign->hasEnded()) {
            return __('games.award.not_ended');
        }

        if ($campaign->prizes()->count() === 0) {
            return __('games.award.no_prizes');
        }

        $pending = $this->pendingReviewThatCouldPlace($campaign);

        if ($pending > 0) {
            return trans_choice('games.award.pending_reviews', $pending, ['count' => $pending]);
        }

        return null;
    }

    /** @return list<GameAward> the awards created, already notified where possible */
    public function finalize(GameCampaign $campaign, ?User $by = null): array
    {
        if ($reason = $this->blocker($campaign)) {
            throw new GameException('cannot_award', $reason);
        }

        $prizes = $campaign->prizes()->get()->keyBy('rank');
        $rows = $this->leaderboard->top($campaign, (int) $prizes->keys()->max());

        $awards = DB::transaction(function () use ($campaign, $prizes, $rows, $by) {
            $created = [];

            foreach ($rows as $row) {
                /** @var GamePrize|null $prize */
                $prize = $prizes->get($row['rank']);

                if (! $prize) {
                    continue;
                }

                $promo = $this->createPromoCode($campaign, $prize, $row['mobile']);

                $created[] = GameAward::create([
                    'game_campaign_id' => $campaign->id,
                    'game_player_id' => $row['player_id'],
                    'game_attempt_id' => $row['attempt_id'],
                    'game_prize_id' => $prize->id,
                    'rank' => $row['rank'],
                    'promo_code_id' => $promo->id,
                    'status' => GameAward::STATUS_AWARDED,
                ]);
            }

            $campaign->forceFill(['awarded_at' => now(), 'awarded_by' => $by?->id])->save();

            return $created;
        });

        foreach ($awards as $award) {
            $this->notify($award);
        }

        return $awards;
    }

    /** Send (or resend) the winner their voucher. Never throws; the outcome is stored on the award. */
    public function notify(GameAward $award): GameAward
    {
        $award->loadMissing(['player', 'campaign', 'prize', 'promoCode']);

        if ($award->status === GameAward::STATUS_VOID || ! $award->promoCode) {
            return $award;
        }

        $locale = $award->player->locale ?: app()->getLocale();
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            $promo = $award->promoCode;
            $text = __('games.messages.award', [
                'name' => Str::of($award->player->name)->trim()->explode(' ')->first(),
                'rank' => $award->rank,
                'campaign' => $award->campaign->translated('name'),
                'discount' => $award->prize?->discountLabel() ?? '',
                'code' => $promo->code,
                'until' => $promo->valid_until ? $promo->valid_until->translatedFormat('j F Y') : __('games.award.no_expiry'),
                'site' => BookingSetting::get('site_name_' . $locale, config('app.name')),
            ]);
        } finally {
            app()->setLocale($previous);
        }

        $result = $this->messenger->send($award->player->mobile, $text, 'game_award');

        $award->forceFill($result->ok
            ? ['status' => GameAward::STATUS_NOTIFIED, 'notified_at' => now(), 'notify_channel' => $result->channel, 'notify_error' => null]
            : ['notify_error' => Str::limit((string) $result->error, 250)]
        )->save();

        return $award;
    }

    /** Take a prize back: the code stops working at once. */
    public function void(GameAward $award): void
    {
        $award->promoCode?->update(['is_active' => false]);
        $award->update(['status' => GameAward::STATUS_VOID]);
    }

    private function createPromoCode(GameCampaign $campaign, GamePrize $prize, string $mobile): PromoCode
    {
        do {
            $code = 'WIN' . Str::upper(Str::random(7));
        } while (PromoCode::withTrashed()->where('code', $code)->exists());

        $name = Str::limit($campaign->getTranslation('name', 'en', false) ?: $campaign->translated('name'), 200, '') . ' #' . $prize->rank;

        return PromoCode::create([
            'code' => $code,
            'name' => $name,
            'description' => Str::limit((string) ($prize->getTranslation('title', 'en', false) ?: $prize->discountLabel()), 250, ''),
            'discount_type' => $prize->discount_type,
            'discount_value' => $prize->discount_value,
            'max_discount_amount' => $prize->max_discount_amount,
            'max_uses' => 1,
            'valid_from' => now(),
            'valid_until' => $prize->expiresAt(now()),
            'is_active' => true,
            'event_id' => $prize->event_id,
            'restricted_mobile' => $mobile,
            'source' => 'game',
        ]);
    }

    /**
     * Attempts waiting for review whose score could reach a prize rank: finalizing before they are
     * looked at could skip someone who really won (or reward a cheat after a clear).
     */
    private function pendingReviewThatCouldPlace(GameCampaign $campaign): int
    {
        $lastPrizeRank = (int) $campaign->prizes()->max('rank');
        $rows = $this->leaderboard->top($campaign, $lastPrizeRank);
        $cut = count($rows) < $lastPrizeRank ? null : end($rows);

        return GameAttempt::query()
            ->where('game_campaign_id', $campaign->id)
            ->where('status', GameAttempt::STATUS_COMPLETED)
            ->where('review_status', GameAttempt::REVIEW_FLAGGED)
            ->when($cut, fn ($q) => $q->where(fn ($q) => $q->where('goals', '>', $cut['goals'])
                ->orWhere(fn ($q) => $q->where('goals', $cut['goals'])->where('duration_ms', '<=', $cut['duration_ms']))))
            ->count();
    }
}

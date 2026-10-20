<?php

namespace Tests\Feature\Games;

use App\Models\GameAttempt;
use App\Models\GameAward;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use App\Models\GamePrize;
use App\Models\MessageLog;
use App\Services\Games\GameAwardService;
use App\Services\Games\GameException;
use App\Services\PromoCodeService;

class GameAwardTest extends GameTestCase
{
    private function completed(GameCampaign $campaign, GamePlayer $player, int $goals, int $ms, array $extra = []): GameAttempt
    {
        return GameAttempt::create(array_merge([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'game_campaign_id' => $campaign->id,
            'game_player_id' => $player->id,
            'status' => GameAttempt::STATUS_COMPLETED,
            'seed' => 1,
            'shots_taken' => 5,
            'goals' => $goals,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(4),
            'duration_ms' => $ms,
        ], $extra));
    }

    private function endedCampaignWithPrizes(): GameCampaign
    {
        $campaign = $this->campaign(['starts_at' => now()->subDays(3), 'ends_at' => now()->subHour()]);

        GamePrize::create(['game_campaign_id' => $campaign->id, 'rank' => 1, 'discount_type' => 'percentage', 'discount_value' => 50, 'max_discount_amount' => 10, 'valid_days' => 30]);
        GamePrize::create(['game_campaign_id' => $campaign->id, 'rank' => 2, 'discount_type' => 'percentage', 'discount_value' => 25, 'valid_days' => 30]);
        GamePrize::create(['game_campaign_id' => $campaign->id, 'rank' => 3, 'discount_type' => 'fixed', 'discount_value' => 2]);

        return $campaign;
    }

    public function test_finalizing_gives_each_prize_rank_a_voucher_bound_to_the_winners_mobile(): void
    {
        $campaign = $this->endedCampaignWithPrizes();
        $gold = $this->player('96895512928');
        $silver = $this->player('96899887766');
        $bronze = $this->player('96891112222');
        $fourth = $this->player('96893334444');
        $this->completed($campaign, $silver, 4, 40000);
        $this->completed($campaign, $gold, 5, 52000);
        $this->completed($campaign, $bronze, 4, 45000);
        $this->completed($campaign, $fourth, 3, 30000);

        $awards = app(GameAwardService::class)->finalize($campaign);

        $this->assertCount(3, $awards);
        $this->assertSame([$gold->id, $silver->id, $bronze->id], collect($awards)->pluck('game_player_id')->all());
        $this->assertNotNull($campaign->fresh()->awarded_at);

        $voucher = $awards[0]->promoCode;
        $this->assertSame('96895512928', $voucher->restricted_mobile);
        $this->assertSame(1, $voucher->max_uses);
        $this->assertSame('game', $voucher->source);
        $this->assertTrue($voucher->valid_until->isAfter(now()->addDays(29)));

        // the winners were messaged (demo SMS driver), with the code in the text
        $this->assertSame(GameAward::STATUS_NOTIFIED, $awards[0]->fresh()->status);
        $this->assertStringContainsString($voucher->code, MessageLog::where('type', 'game_award')->first()->message);
    }

    public function test_a_winners_voucher_only_works_with_their_mobile_and_respects_the_cap(): void
    {
        $campaign = $this->endedCampaignWithPrizes();
        $this->completed($campaign, $this->player('96895512928'), 5, 50000);
        $voucher = app(GameAwardService::class)->finalize($campaign)[0]->promoCode;
        $promo = app(PromoCodeService::class);
        $eventId = 1; // the prize is not limited to an event

        $this->assertFalse($promo->validate($voucher->code, $eventId, 100, '99998888')['valid']);
        $this->assertSame('restricted_mobile', $promo->validate($voucher->code, $eventId, 100, null)['reason']);

        $valid = $promo->validate($voucher->code, $eventId, 100, '9551 2928');
        $this->assertTrue($valid['valid']);
        $this->assertEquals(10.0, $valid['discount_amount']); // 50% of 100, capped at 10
    }

    public function test_finalizing_waits_until_the_campaign_ends_and_reviews_are_done(): void
    {
        $live = $this->campaign(['slug' => 'live']);
        GamePrize::create(['game_campaign_id' => $live->id, 'rank' => 1, 'discount_type' => 'percentage', 'discount_value' => 50]);
        $this->assertSame(__('games.award.not_ended'), app(GameAwardService::class)->blocker($live));

        $campaign = $this->endedCampaignWithPrizes();
        $this->completed($campaign, $this->player('96895512928'), 3, 50000);
        $this->completed($campaign, $this->player('96899887766'), 5, 30000, ['review_status' => GameAttempt::REVIEW_FLAGGED]);

        $this->expectException(GameException::class);
        app(GameAwardService::class)->finalize($campaign);
    }

    public function test_voiding_an_award_disables_its_voucher(): void
    {
        $campaign = $this->endedCampaignWithPrizes();
        $this->completed($campaign, $this->player('96895512928'), 5, 50000);
        $award = app(GameAwardService::class)->finalize($campaign)[0];

        app(GameAwardService::class)->void($award);

        $this->assertSame(GameAward::STATUS_VOID, $award->fresh()->status);
        $this->assertFalse($award->promoCode->fresh()->is_active);
    }
}

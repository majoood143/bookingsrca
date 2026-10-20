<?php

namespace Tests\Feature\Games;

use App\Models\GameAttempt;
use App\Services\Games\LeaderboardService;
use Illuminate\Support\Carbon;

class GameAttemptTest extends GameTestCase
{
    /** Start an attempt and play all its shots with human-paced gaps; returns the last response. */
    private function playAttempt(int $goals = 3, ?\Closure $beforeShot = null)
    {
        $uuid = $this->postJson(route('games.api.start', 'penalty'), ['device' => 'test-device'])->assertOk()->json('attempt');
        $attempt = GameAttempt::where('uuid', $uuid)->firstOrFail();
        $response = null;

        for ($n = 1; $n <= 5; $n++) {
            Carbon::setTestNow(now()->addMilliseconds(2600));
            $beforeShot && $beforeShot($n);
            $swipe = $this->swipeWithOutcome($attempt->seed, $n, 50, $n <= $goals);
            $response = $this->postJson(route('games.api.shot', ['penalty', $uuid]), $swipe)->assertOk();
        }

        Carbon::setTestNow();

        return $response;
    }

    public function test_starting_needs_a_verified_player(): void
    {
        $this->campaign();

        $this->postJson(route('games.api.start', 'penalty'))->assertStatus(401)->assertJsonPath('error', 'sign_in_required');
    }

    public function test_the_server_decides_each_shot_and_times_the_attempt(): void
    {
        $this->campaign();
        $this->signIn($player = $this->player());

        $last = $this->playAttempt(goals: 3);

        $last->assertJsonPath('done', true)
            ->assertJsonPath('result.goals', 3)
            ->assertJsonPath('result.standing.rank', 1)
            ->assertJsonPath('result.under_review', false);

        $attempt = GameAttempt::firstOrFail();
        $this->assertSame(GameAttempt::STATUS_COMPLETED, $attempt->status);
        $this->assertSame(3, $attempt->goals);
        $this->assertSame(5, $attempt->shots()->count());
        $this->assertGreaterThanOrEqual(13000, $attempt->duration_ms);
        $this->assertSame(0, $attempt->risk_score);
    }

    public function test_the_client_never_sees_the_seed(): void
    {
        $this->campaign();
        $this->signIn($this->player());

        $start = $this->postJson(route('games.api.start', 'penalty'))->assertOk();
        $this->assertArrayNotHasKey('seed', $start->json());

        $shot = $this->postJson(route('games.api.shot', ['penalty', $start->json('attempt')]), $this->swipe(1))->assertOk();
        $this->assertStringNotContainsString('seed', $shot->getContent());
    }

    public function test_shots_must_come_in_order_and_only_once(): void
    {
        $this->campaign();
        $this->signIn($this->player());
        $uuid = $this->postJson(route('games.api.start', 'penalty'))->json('attempt');

        $this->postJson(route('games.api.shot', ['penalty', $uuid]), $this->swipe(2))->assertStatus(409);
        $this->postJson(route('games.api.shot', ['penalty', $uuid]), $this->swipe(1))->assertOk();
        $this->postJson(route('games.api.shot', ['penalty', $uuid]), $this->swipe(1))->assertStatus(409);
    }

    public function test_another_players_attempt_cannot_be_played(): void
    {
        $this->campaign();
        $this->signIn($this->player());
        $uuid = $this->postJson(route('games.api.start', 'penalty'))->json('attempt');

        $this->signIn($this->player('96899998888'));
        $this->postJson(route('games.api.shot', ['penalty', $uuid]), $this->swipe(1))->assertNotFound();
    }

    public function test_starting_uses_an_attempt_and_the_daily_limit_holds(): void
    {
        $this->campaign(['attempts_per_day' => 2]);
        $this->signIn($this->player());

        $this->postJson(route('games.api.start', 'penalty'))->assertOk();
        // refreshing half way: the first attempt is abandoned but still counted
        $this->postJson(route('games.api.start', 'penalty'))->assertOk()->assertJsonPath('remaining.today', 0);
        $this->postJson(route('games.api.start', 'penalty'))->assertStatus(422)->assertJsonPath('error', 'no_attempts_left');

        $this->assertSame(1, GameAttempt::where('status', GameAttempt::STATUS_ABANDONED)->count());
    }

    public function test_closed_campaigns_and_blocked_players_cannot_start(): void
    {
        $campaign = $this->campaign(['ends_at' => now()->subMinute()]);
        $this->signIn($player = $this->player());

        $this->postJson(route('games.api.start', 'penalty'))->assertStatus(422)->assertJsonPath('error', 'campaign_ended');

        $campaign->update(['ends_at' => now()->addDay()]);
        $player->update(['blocked_at' => now()]);
        $this->postJson(route('games.api.start', 'penalty'))->assertStatus(403)->assertJsonPath('error', 'player_blocked');
    }

    public function test_scripted_play_is_held_for_review_and_kept_off_the_leaderboard(): void
    {
        $campaign = $this->campaign();
        $this->signIn($this->player());

        $uuid = $this->postJson(route('games.api.start', 'penalty'))->json('attempt');
        $bot = ['u' => 0.4, 'v' => 0.85, 'speed' => 1.9, 'ms' => 5, 'points' => [[0, 0, 0], [0.4, -0.85, 5]], 'webdriver' => true];

        for ($n = 1; $n <= 5; $n++) {
            $last = $this->postJson(route('games.api.shot', ['penalty', $uuid]), ['n' => $n] + $bot)->assertOk();
        }

        $last->assertJsonPath('result.under_review', true);
        $attempt = GameAttempt::firstOrFail();
        $this->assertSame(GameAttempt::REVIEW_FLAGGED, $attempt->review_status);
        $this->assertSame(100, $attempt->risk_score);
        $this->assertContains('too_fast', $attempt->flags);
        $this->assertSame([], app(LeaderboardService::class)->publicBoard($campaign)['rows']);
    }

    public function test_the_public_leaderboard_ranks_best_attempts_with_masked_mobiles(): void
    {
        $this->campaign();

        $this->signIn($first = $this->player('96895512928'));
        $this->playAttempt(goals: 2);
        $this->playAttempt(goals: 4); // the best attempt counts

        $this->signIn($second = $this->player('96899887766', ['name' => 'Maryam']));
        $this->playAttempt(goals: 3);

        $this->getJson(route('games.api.leaderboard', 'penalty'))
            ->assertOk()
            ->assertJsonPath('players', 2)
            ->assertJsonPath('rows.0.player', '955**928')
            ->assertJsonPath('rows.0.goals', 4)
            ->assertJsonPath('rows.1.player', '998**766')
            ->assertJsonPath('me.rank', 2)
            ->assertJsonMissing(['mobile' => '96895512928'])
            ->assertDontSee('Salim');
    }

    public function test_equal_goals_are_split_by_the_faster_time(): void
    {
        $this->campaign();

        $this->signIn($this->player('96895512928'));
        $this->playAttempt(goals: 3, beforeShot: fn () => Carbon::setTestNow(now()->addSeconds(3)));

        $this->signIn($this->player('96899887766'));
        $this->playAttempt(goals: 3);

        $this->getJson(route('games.api.leaderboard', 'penalty'))
            ->assertJsonPath('rows.0.player', '998**766')
            ->assertJsonPath('rows.1.player', '955**928');
    }

    public function test_the_leaderboard_page_renders_for_stadium_screens(): void
    {
        $this->campaign();

        $this->get(route('games.leaderboard', ['campaign' => 'penalty', 'display' => 1]))
            ->assertOk()
            ->assertSee('lb-display', false)
            ->assertSee('data:image/png;base64', false);
    }
}

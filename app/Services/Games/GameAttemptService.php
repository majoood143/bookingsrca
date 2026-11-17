<?php

namespace App\Services\Games;

use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use App\Models\GameShot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Runs attempts: starts one when the campaign is open and the player has attempts left, takes
 * each shot in order (the engine decides it), and closes the attempt with the server-measured
 * time and its risk score. Starting an attempt uses it up, so refreshing until a lucky run
 * does not work.
 */
class GameAttemptService
{
    /** More players than this on one device in a campaign marks their attempts for review. */
    public const MAX_PLAYERS_PER_DEVICE = 2;

    public function __construct(
        private readonly PenaltyEngine $engine,
        private readonly RiskScorer $risk,
        private readonly LeaderboardService $leaderboard,
    ) {}

    /**
     * Attempts the player may still start: today's and in total, null meaning unlimited.
     *
     * @return array{today: ?int, total: ?int, can_play: bool}
     */
    public function remaining(GameCampaign $campaign, GamePlayer $player): array
    {
        $attempts = GameAttempt::query()->where('game_campaign_id', $campaign->id)->where('game_player_id', $player->id);

        $today = $campaign->attempts_per_day === null
            ? null
            : max(0, $campaign->attempts_per_day - (clone $attempts)->where('started_at', '>=', now()->startOfDay())->count());

        $total = $campaign->attempts_total === null
            ? null
            : max(0, $campaign->attempts_total - (clone $attempts)->count());

        return ['today' => $today, 'total' => $total, 'can_play' => $today !== 0 && $total !== 0];
    }

    public function start(GameCampaign $campaign, GamePlayer $player, ?string $ip, ?string $userAgent, ?string $deviceHash): GameAttempt
    {
        if (! $campaign->isLive()) {
            throw new GameException($campaign->hasEnded() ? 'campaign_ended' : 'campaign_not_open');
        }

        if ($player->isBlocked()) {
            throw new GameException('player_blocked', status: 403);
        }

        return DB::transaction(function () use ($campaign, $player, $ip, $userAgent, $deviceHash) {
            // one attempt at a time: serialise starts for this player
            GamePlayer::query()->whereKey($player->id)->lockForUpdate()->first();

            $this->abandonOpen($campaign, $player);

            if (! $this->remaining($campaign, $player)['can_play']) {
                throw new GameException('no_attempts_left');
            }

            $flags = [];

            if ($deviceHash !== null) {
                $playersOnDevice = GameAttempt::query()
                    ->where('game_campaign_id', $campaign->id)
                    ->where('device_hash', $deviceHash)
                    ->where('game_player_id', '!=', $player->id)
                    ->distinct()
                    ->count('game_player_id');

                if ($playersOnDevice >= self::MAX_PLAYERS_PER_DEVICE) {
                    $flags[] = 'shared_device';
                }
            }

            return GameAttempt::create([
                'uuid' => (string) Str::uuid(),
                'game_campaign_id' => $campaign->id,
                'game_player_id' => $player->id,
                'status' => GameAttempt::STATUS_STARTED,
                'seed' => random_int(0, 4294967295),
                'started_at' => Carbon::now(),
                'flags' => $flags,
                'risk_score' => $this->risk->score($flags),
                'ip' => $ip,
                'user_agent' => $userAgent ? Str::limit($userAgent, 250, '') : null,
                'device_hash' => $deviceHash,
            ]);
        });
    }

    /**
     * Decide shot $n of the attempt from the player's swipe.
     *
     * @param  array{u: float, v: float, speed: float, ms: int, points?: array, webdriver?: bool}  $input
     * @return array<string, mixed> the shot for the browser to animate, and the result after the last one
     */
    public function shoot(GameAttempt $attempt, int $n, array $input): array
    {
        $result = DB::transaction(function () use ($attempt, $n, $input) {
            /** @var GameAttempt $attempt */
            $attempt = GameAttempt::query()->whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $campaign = $attempt->campaign;

            // The same shot again (the browser retried after a dropped connection): answer with the
            // shot already decided, never decide it twice.
            if ($n >= 1 && $n === $attempt->shots_taken && $stored = $attempt->shots()->where('n', $n)->first()) {
                return [
                    'shot' => $this->replay($stored),
                    'attempt' => $attempt,
                    'done' => $attempt->status === GameAttempt::STATUS_COMPLETED,
                ];
            }

            if (! $attempt->isOpen()) {
                throw new GameException('attempt_closed');
            }

            if ($attempt->started_at->lt(now()->subMinutes(GameAttempt::ABANDON_AFTER_MINUTES))) {
                $attempt->update(['status' => GameAttempt::STATUS_ABANDONED]);

                throw new GameException('attempt_expired');
            }

            if ($n !== $attempt->shots_taken + 1 || $n > $campaign->shots_per_attempt) {
                throw new GameException('shot_out_of_order', status: 409);
            }

            $now = Carbon::now();
            $gapMs = $n === 1 ? null : (int) round(($now->getPreciseTimestamp(3) - $attempt->last_shot_at->getPreciseTimestamp(3)));
            $previous = $attempt->shots()->pluck('input')->all();
            $shotFlags = $this->risk->shotFlags($input, $gapMs, $previous);

            $shot = $this->engine->shoot($attempt->seed, $n, $campaign->keeper_skill, $input);

            GameShot::create([
                'game_attempt_id' => $attempt->id,
                'n' => $n,
                'input' => $input + ['flags' => $shotFlags],
                'aim_x' => $shot['aim']['x'],
                'aim_y' => $shot['aim']['y'],
                'power' => $shot['power'],
                'keeper' => $shot['keeper'],
                'outcome' => $shot['outcome'],
                'gap_ms' => $gapMs,
            ]);

            $flags = array_merge($attempt->flags ?? [], $shotFlags);
            $done = $n === $campaign->shots_per_attempt;

            $attempt->fill([
                'shots_taken' => $n,
                'goals' => $attempt->goals + ($shot['outcome'] === 'goal' ? 1 : 0),
                'last_shot_at' => $now,
                'flags' => $flags,
                'risk_score' => $this->risk->score($flags),
            ]);

            if ($done) {
                $attempt->fill([
                    'status' => GameAttempt::STATUS_COMPLETED,
                    'finished_at' => $now,
                    'duration_ms' => (int) round($now->getPreciseTimestamp(3) - $attempt->started_at->getPreciseTimestamp(3)),
                    'review_status' => $attempt->risk_score >= $campaign->risk_threshold ? GameAttempt::REVIEW_FLAGGED : null,
                ]);
            }

            $attempt->save();

            return ['shot' => $shot, 'attempt' => $attempt, 'done' => $done];
        });

        /** @var GameAttempt $attempt */
        $attempt = $result['attempt'];

        $payload = [
            'n' => $n,
            'outcome' => $result['shot']['outcome'],
            'aim' => $result['shot']['aim'],
            'power' => $result['shot']['power'],
            'keeper' => $result['shot']['keeper'],
            'flight' => $result['shot']['flight'],
            'goals' => $attempt->goals,
            'shots_taken' => $attempt->shots_taken,
            'done' => $result['done'],
        ];

        if ($result['done']) {
            $this->leaderboard->forget($attempt->campaign);
            $payload['result'] = $this->result($attempt);
        }

        return $payload;
    }

    /** @return array<string, mixed> the finished attempt and where the player now stands */
    public function result(GameAttempt $attempt): array
    {
        $standing = $this->leaderboard->standing($attempt->campaign, $attempt->player);

        return [
            'goals' => $attempt->goals,
            'duration_ms' => $attempt->duration_ms,
            'under_review' => $attempt->review_status === GameAttempt::REVIEW_FLAGGED,
            'is_best' => $standing !== null && $standing['attempt_id'] === $attempt->id,
            'standing' => $standing,
            'shots' => $attempt->shots()->get(['n', 'outcome'])->map(fn (GameShot $s) => ['n' => $s->n, 'outcome' => $s->outcome])->all(),
            'remaining' => $this->remaining($attempt->campaign, $attempt->player),
        ];
    }

    /** A stored shot in the engine's answer format, for a retried request. */
    private function replay(GameShot $shot): array
    {
        return [
            'aim' => ['x' => $shot->aim_x, 'y' => $shot->aim_y],
            'power' => $shot->power,
            'outcome' => $shot->outcome,
            'keeper' => $shot->keeper,
            'flight' => PenaltyEngine::flight($shot->power),
        ];
    }

    /** Close the player's unfinished attempts in this campaign; they still count as played. */
    private function abandonOpen(GameCampaign $campaign, GamePlayer $player): void
    {
        GameAttempt::query()
            ->where('game_campaign_id', $campaign->id)
            ->where('game_player_id', $player->id)
            ->where('status', GameAttempt::STATUS_STARTED)
            ->update(['status' => GameAttempt::STATUS_ABANDONED, 'updated_at' => now()]);
    }
}

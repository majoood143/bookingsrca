<?php

namespace App\Services\Games;

use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use App\Support\PhoneNumber;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Who leads a campaign. Each player's best eligible attempt counts: most goals, then the fastest
 * server-measured time, then whoever got there first. Blocked players and attempts held for
 * review (or thrown out) do not rank. Public screens show masked mobiles only (955**928).
 */
class LeaderboardService
{
    /** Seconds a public board is cached; finishing an attempt clears it at once. */
    public const CACHE_SECONDS = 15;

    /**
     * Every player's best eligible attempt, one row each, unordered.
     *
     * @param  \DateTimeInterface|null  $cutoff  only attempts finished by then (a frozen board)
     */
    public function bestAttempts(GameCampaign $campaign, ?\DateTimeInterface $cutoff = null): Builder
    {
        $ranked = DB::table('game_attempts as a')
            ->join('game_players as p', 'p.id', '=', 'a.game_player_id')
            ->where('a.game_campaign_id', $campaign->id)
            ->where('a.status', GameAttempt::STATUS_COMPLETED)
            ->where(fn ($q) => $q->whereNull('a.review_status')->orWhere('a.review_status', GameAttempt::REVIEW_CLEARED))
            ->whereNull('p.blocked_at')
            ->when($cutoff, fn ($q) => $q->where('a.finished_at', '<=', $cutoff))
            ->select([
                'a.id as attempt_id',
                'a.game_player_id as player_id',
                'a.goals',
                'a.duration_ms',
                'a.finished_at',
                'p.mobile',
                'p.name',
            ])
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY a.game_player_id ORDER BY a.goals DESC, a.duration_ms ASC, a.finished_at ASC, a.id ASC) as player_rank');

        return DB::query()->fromSub($ranked, 'best')->where('player_rank', 1);
    }

    /**
     * The top of the board, in order.
     *
     * @return list<array{rank: int, attempt_id: int, player_id: int, mobile: string, name: string, goals: int, duration_ms: int, finished_at: string}>
     */
    public function top(GameCampaign $campaign, int $limit, ?\DateTimeInterface $cutoff = null): array
    {
        $rows = $this->ordered($this->bestAttempts($campaign, $cutoff))->limit($limit)->get();

        return $rows->values()->map(fn ($row, $i) => [
            'rank' => $i + 1,
            'attempt_id' => (int) $row->attempt_id,
            'player_id' => (int) $row->player_id,
            'mobile' => (string) $row->mobile,
            'name' => (string) $row->name,
            'goals' => (int) $row->goals,
            'duration_ms' => (int) $row->duration_ms,
            'finished_at' => (string) $row->finished_at,
        ])->all();
    }

    /**
     * The public board: masked mobiles, frozen when the campaign says so, cached briefly.
     *
     * @return array{rows: list<array{rank: int, mobile: string, goals: int, duration_ms: int}>, players: int, frozen: bool, updated_at: string}
     */
    public function publicBoard(GameCampaign $campaign): array
    {
        return Cache::remember($this->cacheKey($campaign), self::CACHE_SECONDS, function () use ($campaign) {
            $cutoff = $campaign->isFrozen() ? $campaign->leaderboard_freeze_at : null;
            $rows = $this->top($campaign, max(1, $campaign->leaderboard_size), $cutoff);

            return [
                'rows' => array_map(fn (array $r) => [
                    'rank' => $r['rank'],
                    'player' => PhoneNumber::mask($r['mobile']),
                    'goals' => $r['goals'],
                    'duration_ms' => $r['duration_ms'],
                ], $rows),
                'players' => $this->bestAttempts($campaign, $cutoff)->count(),
                'frozen' => $cutoff !== null,
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Where a player stands (live, not frozen), or null before they have an eligible attempt.
     *
     * @return array{rank: int, of: int, attempt_id: int, goals: int, duration_ms: int, leader_goals: int, leader_duration_ms: int}|null
     */
    public function standing(GameCampaign $campaign, GamePlayer $player): ?array
    {
        $best = $this->bestAttempts($campaign);
        $mine = (clone $best)->where('player_id', $player->id)->first();

        if (! $mine) {
            return null;
        }

        $ahead = (clone $best)->where(function ($q) use ($mine) {
            $q->where('goals', '>', $mine->goals)
                ->orWhere(fn ($q) => $q->where('goals', $mine->goals)->where('duration_ms', '<', $mine->duration_ms))
                ->orWhere(fn ($q) => $q->where('goals', $mine->goals)->where('duration_ms', $mine->duration_ms)->where('finished_at', '<', $mine->finished_at))
                ->orWhere(fn ($q) => $q->where('goals', $mine->goals)->where('duration_ms', $mine->duration_ms)->where('finished_at', $mine->finished_at)->where('attempt_id', '<', $mine->attempt_id));
        })->count();

        $leader = $this->ordered(clone $best)->first();

        return [
            'rank' => $ahead + 1,
            'of' => (clone $best)->count(),
            'attempt_id' => (int) $mine->attempt_id,
            'goals' => (int) $mine->goals,
            'duration_ms' => (int) $mine->duration_ms,
            'leader_goals' => (int) $leader->goals,
            'leader_duration_ms' => (int) $leader->duration_ms,
        ];
    }

    public function forget(GameCampaign $campaign): void
    {
        Cache::forget($this->cacheKey($campaign));
    }

    private function ordered(Builder $query): Builder
    {
        return $query->orderByDesc('goals')->orderBy('duration_ms')->orderBy('finished_at')->orderBy('attempt_id');
    }

    private function cacheKey(GameCampaign $campaign): string
    {
        return "games.leaderboard.{$campaign->id}";
    }
}

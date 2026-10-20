<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One play-through of a campaign. The server holds the seed and decides every shot, so the score
 * and the server-measured duration are the official result. An attempt counts against the
 * player's limits as soon as it starts; leaving half way through leaves it "abandoned".
 */
class GameAttempt extends Model
{
    public const STATUS_STARTED = 'started';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    /** High risk score: kept off the leaderboard until an admin looks at it. */
    public const REVIEW_FLAGGED = 'flagged';

    public const REVIEW_CLEARED = 'cleared';

    public const REVIEW_INVALIDATED = 'invalidated';

    /** An attempt nobody finished within this many minutes is abandoned. */
    public const ABANDON_AFTER_MINUTES = 15;

    protected $fillable = [
        'uuid',
        'game_campaign_id',
        'game_player_id',
        'status',
        'seed',
        'shots_taken',
        'goals',
        'started_at',
        'last_shot_at',
        'finished_at',
        'duration_ms',
        'risk_score',
        'flags',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'ip',
        'user_agent',
        'device_hash',
    ];

    protected $hidden = ['seed'];

    /** Milliseconds kept on started/finished times: the time tie-break is measured in them. */
    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected $casts = [
        'seed' => 'integer',
        'shots_taken' => 'integer',
        'goals' => 'integer',
        'started_at' => 'datetime',
        'last_shot_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
        'risk_score' => 'integer',
        'flags' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GameCampaign::class, 'game_campaign_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function shots(): HasMany
    {
        return $this->hasMany(GameShot::class)->orderBy('n');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Completed and not held back or thrown out by a review: counts for the leaderboard and prizes. */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->where(fn (Builder $q) => $q->whereNull('review_status')->orWhere('review_status', self::REVIEW_CLEARED));
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_STARTED;
    }

    /** 41370 → "0:41.37", the way the game shows times. */
    public static function formatDuration(?int $ms): string
    {
        if ($ms === null) {
            return '—';
        }

        $seconds = $ms / 1000;
        $minutes = (int) floor($seconds / 60);

        return $minutes . ':' . str_pad(number_format($seconds - $minutes * 60, 2, '.', ''), 5, '0', STR_PAD_LEFT);
    }

    public function isEligible(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && in_array($this->review_status, [null, self::REVIEW_CLEARED], true);
    }
}

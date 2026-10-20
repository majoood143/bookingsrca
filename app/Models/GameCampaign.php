<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

/**
 * One run of a game (e.g. "Penalty Challenge – Ramadan"), fully set up by the admin: when it is
 * open, its rules, the prizes for the top ranks and its branding. Anything left empty in the
 * branding falls back to the app's own (General Settings), so a client's install is branded
 * without touching the campaign.
 */
class GameCampaign extends Model
{
    use HasTranslations, LogsActivity, SoftDeletes;

    public const TYPE_PENALTY = 'penalty';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    public const PHASE_DRAFT = 'draft';

    public const PHASE_UPCOMING = 'upcoming';

    public const PHASE_LIVE = 'live';

    public const PHASE_ENDED = 'ended';

    public const DEFAULT_ACCENT = '#ffd76a';

    public const DEFAULT_KEEPER_KIT = '#f08a24';

    protected $fillable = [
        'game_type',
        'name',
        'slug',
        'description',
        'status',
        'starts_at',
        'ends_at',
        'shots_per_attempt',
        'attempts_per_day',
        'attempts_total',
        'keeper_skill',
        'risk_threshold',
        'leaderboard_public',
        'leaderboard_size',
        'leaderboard_freeze_at',
        'primary_color',
        'secondary_color',
        'accent_color',
        'kit_color',
        'keeper_kit_color',
        'logo_path',
        'sponsor_logo_path',
        'cover_image_path',
        'sponsor_name',
        'headline',
        'tagline',
        'board_text',
        'prize_text',
        'terms',
        'share_text',
    ];

    public $translatable = ['name', 'description', 'sponsor_name', 'headline', 'tagline', 'board_text', 'prize_text', 'terms', 'share_text'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'leaderboard_freeze_at' => 'datetime',
        'awarded_at' => 'datetime',
        'shots_per_attempt' => 'integer',
        'attempts_per_day' => 'integer',
        'attempts_total' => 'integer',
        'keeper_skill' => 'integer',
        'risk_threshold' => 'integer',
        'leaderboard_public' => 'boolean',
        'leaderboard_size' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'status', 'starts_at', 'ends_at', 'shots_per_attempt', 'attempts_per_day', 'attempts_total', 'keeper_skill', 'risk_threshold', 'leaderboard_public', 'leaderboard_freeze_at', 'awarded_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(GamePrize::class)->orderBy('rank');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(GameAttempt::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(GameAward::class)->orderBy('rank');
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->published()->where('starts_at', '<=', now())->where('ends_at', '>', now());
    }

    public function phase(): string
    {
        return match (true) {
            $this->status !== self::STATUS_PUBLISHED => self::PHASE_DRAFT,
            $this->starts_at->isFuture() => self::PHASE_UPCOMING,
            $this->ends_at->isPast() => self::PHASE_ENDED,
            default => self::PHASE_LIVE,
        };
    }

    public function isLive(): bool
    {
        return $this->phase() === self::PHASE_LIVE;
    }

    public function hasEnded(): bool
    {
        return $this->ends_at->isPast();
    }

    /** The leaderboard is frozen: later attempts still count for prizes but the public board stops moving. */
    public function isFrozen(): bool
    {
        return $this->leaderboard_freeze_at !== null && $this->leaderboard_freeze_at->isPast();
    }

    public function isAwarded(): bool
    {
        return $this->awarded_at !== null;
    }

    /**
     * Colors, logos and names for the game and leaderboard screens: the campaign's own when set,
     * else the app's branding from General Settings.
     *
     * @return array<string, string|null>
     */
    public function brand(): array
    {
        $primary = $this->primary_color ?: BookingSetting::get('primary_color', '#05602b');
        $siteLogo = BookingSetting::get('site_logo') ?: BookingSetting::get('app_logo');
        $storage = fn (?string $path) => $path ? asset('storage/' . $path) : null;

        return [
            'primary' => $primary,
            'secondary' => $this->secondary_color ?: BookingSetting::get('secondary_color', '#0da74c'),
            'accent' => $this->accent_color ?: self::DEFAULT_ACCENT,
            'kit' => $this->kit_color ?: $primary,
            'keeper_kit' => $this->keeper_kit_color ?: self::DEFAULT_KEEPER_KIT,
            'logo' => $storage($this->logo_path) ?? $storage($siteLogo),
            'sponsor_logo' => $storage($this->sponsor_logo_path),
            'cover' => $storage($this->cover_image_path) ?? $storage(BookingSetting::get('seo_og_image')),
            'site_name' => BookingSetting::get('site_name_' . app()->getLocale(), config('app.name')),
            'favicon' => $storage(BookingSetting::get('favicon')) ?? asset('favicon.ico'),
        ];
    }

    public function translated(string $field): string
    {
        return (string) ($this->getTranslation($field, app()->getLocale(), false) ?: $this->getTranslation($field, 'en', false) ?: $this->getTranslation($field, 'ar', false));
    }

    public function playUrl(): string
    {
        return route('games.play', $this->slug);
    }

    public function leaderboardUrl(bool $display = false): string
    {
        return route('games.leaderboard', $display ? ['campaign' => $this->slug, 'display' => 1] : $this->slug);
    }
}

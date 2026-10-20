<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A rank's prize given to a player when the campaign is finalized: a promo code bound to their mobile. */
class GameAward extends Model
{
    public const STATUS_AWARDED = 'awarded';

    public const STATUS_NOTIFIED = 'notified';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'game_campaign_id',
        'game_player_id',
        'game_attempt_id',
        'game_prize_id',
        'rank',
        'promo_code_id',
        'status',
        'notified_at',
        'notify_channel',
        'notify_error',
    ];

    protected $casts = [
        'rank' => 'integer',
        'notified_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GameCampaign::class, 'game_campaign_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(GameAttempt::class, 'game_attempt_id');
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(GamePrize::class, 'game_prize_id');
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }
}

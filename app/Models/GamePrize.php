<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * The voucher a rank wins in a campaign: a percentage or fixed discount, optionally capped,
 * optionally limited to one event (the pitch), valid for some days after it is awarded or until
 * a fixed date.
 */
class GamePrize extends Model
{
    use HasTranslations;

    protected $fillable = [
        'game_campaign_id',
        'rank',
        'title',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'event_id',
        'valid_days',
        'valid_until',
    ];

    public $translatable = ['title'];

    protected $casts = [
        'rank' => 'integer',
        'discount_value' => 'decimal:3',
        'max_discount_amount' => 'decimal:3',
        'valid_days' => 'integer',
        'valid_until' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GameCampaign::class, 'game_campaign_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** When a voucher awarded at $awardedAt stops working: the fixed date, else after valid_days, else never. */
    public function expiresAt(CarbonInterface $awardedAt): ?CarbonInterface
    {
        if ($this->valid_until) {
            return $this->valid_until;
        }

        return $this->valid_days ? $awardedAt->copy()->addDays($this->valid_days)->endOfDay() : null;
    }

    /** "50%" or "5.000 OMR", plus the cap when there is one (unless $withCap is false). */
    public function discountLabel(bool $withCap = true): string
    {
        $label = $this->discount_type === 'percentage'
            ? rtrim(rtrim(number_format((float) $this->discount_value, 3, '.', ''), '0'), '.') . '%'
            : number_format((float) $this->discount_value, 3) . ' ' . BookingSetting::currencySymbol();

        $cap = $this->capLabel();

        return $withCap && $cap ? $label . ' (' . $cap . ')' : $label;
    }

    /** "up to 20.000 OMR" for a capped percentage, else null. */
    public function capLabel(): ?string
    {
        if ($this->discount_type !== 'percentage' || $this->max_discount_amount === null) {
            return null;
        }

        return __('games.prize.up_to', ['amount' => number_format((float) $this->max_discount_amount, 3) . ' ' . BookingSetting::currencySymbol()]);
    }
}

<?php

namespace App\Models;

use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Someone who verified their mobile by a one-time code to play. One row per mobile across all
 * campaigns, so a client keeps one list of game customers (Games → Players, exportable).
 */
class GamePlayer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'mobile',
        'name',
        'locale',
        'verified_at',
        'terms_accepted_at',
        'marketing_opt_in',
        'blocked_at',
        'blocked_reason',
        'first_ip',
        'last_ip',
        'last_device_hash',
        'last_seen_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'marketing_opt_in' => 'boolean',
        'blocked_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'mobile', 'marketing_opt_in', 'blocked_at', 'blocked_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(GameAttempt::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(GameAward::class);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function maskedMobile(): string
    {
        return PhoneNumber::mask($this->mobile);
    }

    /** +968 95512928, for admin screens. */
    public function displayMobile(): string
    {
        $local = PhoneNumber::local($this->mobile);

        return $local === $this->mobile ? '+' . $this->mobile : '+' . PhoneNumber::DEFAULT_COUNTRY_CODE . ' ' . $local;
    }
}

<?php

namespace App\Models;

use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class CommissionSetting extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'commission_type',
        'commission_value',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $translatable = ['name', 'description'];

    protected $casts = [
        'commission_value' => 'decimal:3',
        'commission_type' => CommissionType::class,
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'commission_type' => 'percentage',
        'is_active' => true,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['event_id', 'name', 'commission_type', 'commission_value', 'effective_from', 'effective_to', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('event_id');
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeEffectiveOn(Builder $query, $date): Builder
    {
        $date = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date;

        return $query
            ->where(fn (Builder $q) => $q->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
            ->where(fn (Builder $q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date));
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS / METHODS
    |--------------------------------------------------------------------------
    */

    public function getIsGlobalAttribute(): bool
    {
        return is_null($this->event_id);
    }

    /**
     * Apply this setting's rate to a base amount.
     */
    public function calculateAmount(float $baseAmount): float
    {
        if ($this->commission_type === CommissionType::Percentage) {
            return round(($baseAmount * (float) $this->commission_value) / 100, 3);
        }

        return round(min((float) $this->commission_value, $baseAmount), 3);
    }

    public function getFormattedRateAttribute(): string
    {
        return $this->commission_type === CommissionType::Percentage
            ? number_format((float) $this->commission_value, 2) . '%'
            : number_format((float) $this->commission_value, 3) . ' ' . BookingSetting::currencySymbol();
    }
}

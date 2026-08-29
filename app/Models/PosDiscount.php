<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosDiscount extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'type',
        'value',
        'applies_to',
        'requires_approval',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:3',
        'requires_approval' => 'boolean',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'type', 'value', 'applies_to', 'requires_approval', 'max_uses', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class, 'discount_id');
    }

    public function isUsable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function appliesToItemType(string $itemType): bool
    {
        return match ($this->applies_to) {
            'tickets' => $itemType === 'tickets',
            'merchandise' => $itemType === 'merchandise',
            default => true,
        };
    }

    public function calculateDiscount(float $amount): float
    {
        return match ($this->type) {
            'percentage' => round($amount * ((float) $this->value / 100), 3),
            'fixed_amount' => min($amount, (float) $this->value),
            default => 0,
        };
    }
}

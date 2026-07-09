<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class KioskCard extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'uid',
        'holder_name',
        'phone',
        'balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['uid', 'holder_name', 'phone', 'balance', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function transactions()
    {
        return $this->hasMany(KioskCardTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    // Applies a signed balance change and writes the ledger row in one place.
    // Caller is responsible for locking the row (lockForUpdate) and wrapping
    // this in a DB transaction alongside the booking it belongs to.
    public function applyTransaction(string $type, float $signedAmount, array $attributes = []): KioskCardTransaction
    {
        $this->balance = (float) $this->balance + $signedAmount;
        $this->save();

        return $this->transactions()->create(array_merge([
            'type' => $type,
            'amount' => $signedAmount,
            'balance_after' => $this->balance,
        ], $attributes));
    }
}

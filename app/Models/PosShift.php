<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosShift extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'terminal_id',
        'user_id',
        'opening_float',
        'closing_float',
        'expected_cash',
        'counted_cash',
        'variance',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opening_float' => 'decimal:3',
        'closing_float' => 'decimal:3',
        'expected_cash' => 'decimal:3',
        'counted_cash' => 'decimal:3',
        'variance' => 'decimal:3',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['terminal_id', 'user_id', 'opening_float', 'closing_float', 'expected_cash', 'counted_cash', 'variance', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class, 'shift_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    // Cash expected in the drawer at close: opening float plus every cash
    // payment recorded against this shift's transactions.
    public function calculateExpectedCash(): float
    {
        $cashSales = PosPayment::whereIn('transaction_id', $this->transactions()->pluck('id'))
            ->where('method', 'cash')
            ->sum('amount');

        return (float) $this->opening_float + (float) $cashSales;
    }

    public function close(float $countedCash): void
    {
        $expected = $this->calculateExpectedCash();

        $this->update([
            'status' => 'closed',
            'closing_float' => $countedCash,
            'expected_cash' => $expected,
            'counted_cash' => $countedCash,
            'variance' => $countedCash - $expected,
            'closed_at' => now(),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosTax extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'rate', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class, 'order_tax_id');
    }

    public function calculateTax(float $amount): float
    {
        return round($amount * ((float) $this->rate / 100), 3);
    }
}

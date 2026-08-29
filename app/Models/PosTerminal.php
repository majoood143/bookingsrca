<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosTerminal extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'location',
        'printer_config',
        'is_active',
    ];

    protected $casts = [
        'printer_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'location', 'printer_config', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function shifts()
    {
        return $this->hasMany(PosShift::class, 'terminal_id');
    }

    public function transactions()
    {
        return $this->hasMany(PosTransaction::class, 'terminal_id');
    }

    public function openShift()
    {
        return $this->hasOne(PosShift::class, 'terminal_id')->where('status', 'open');
    }
}

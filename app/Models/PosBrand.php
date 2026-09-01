<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosBrand extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'logo',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'name_ar', 'slug', 'is_active', 'order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function products()
    {
        return $this->hasMany(PosProduct::class, 'brand_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getDisplayNameAttribute(): string
    {
        if (app()->getLocale() === 'ar' && $this->name_ar) {
            return $this->name_ar;
        }

        return $this->name;
    }
}

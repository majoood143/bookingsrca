<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosProductCategory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'parent_id',
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
            ->logOnly(['name', 'parent_id', 'is_active', 'order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(PosProduct::class, 'category_id');
    }

    public function isSubCategory(): bool
    {
        return $this->parent_id !== null;
    }

    // "Farm Produce > Vegetables" for a sub-category, or just "Farm Produce" for a top-level one.
    public function getFullNameAttribute(): string
    {
        return $this->parent ? $this->parent->name . ' > ' . $this->name : $this->name;
    }
}

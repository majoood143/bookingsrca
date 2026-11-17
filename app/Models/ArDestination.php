<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class ArDestination extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = ['slug', 'title', 'summary', 'description', 'sort_order', 'is_published'];

    public array $translatable = ['title', 'summary', 'description'];

    protected $casts = ['is_published' => 'boolean'];

    public function zones(): HasMany
    {
        return $this->hasMany(ArZone::class);
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(ArItem::class, ArZone::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->useDisk('public')->singleFile();
        $this->addMediaCollection('gallery')->useDisk('public');
    }
}

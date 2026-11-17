<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class ArItem extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = ['ar_zone_id', 'slug', 'title', 'summary', 'body', 'sort_order', 'is_published'];

    public array $translatable = ['title', 'summary', 'body'];

    protected $casts = ['is_published' => 'boolean'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ArZone::class, 'ar_zone_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function registerMediaCollections(): void
    {
        // The photo the camera recognises.
        $this->addMediaCollection('target')->useDisk('public')->singleFile();
        $this->addMediaCollection('gallery')->useDisk('public');
        // One narration file per language, tagged with the `locale` custom property.
        $this->addMediaCollection('audio')->useDisk('public');
    }

    /** The narration for a language; no fallback, so a visitor never hears the wrong language. */
    public function audioUrl(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $this->getMedia('audio')
            ->first(fn (Media $media) => $media->getCustomProperty('locale') === $locale)
            ?->getUrl();
    }
}

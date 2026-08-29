<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class CmsMenuItem extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'menu_id',
        'label',
        'url',
        'linkable_type',
        'linkable_id',
        'icon',
        'is_active',
        'parent_id',
        'sort_order',
    ];

    protected $translatable = ['label'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function resolveUrl(): string
    {
        if ($this->linkable instanceof CmsPage) {
            return $this->linkable->is_homepage ? '/' : '/'.$this->linkable->slug;
        }

        if ($this->linkable instanceof CmsPost) {
            return '/blog/'.$this->linkable->slug;
        }

        if ($this->linkable instanceof CmsCategory) {
            return '/blog/category/'.$this->linkable->slug;
        }

        return $this->url ?? '#';
    }
}

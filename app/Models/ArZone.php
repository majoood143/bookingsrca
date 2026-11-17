<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * One MindAR target file. MindAR reports a found image by its index in that file, so
 * `target_order` keeps the item id for each index from the moment it was compiled.
 */
class ArZone extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    // MindAR slows down noticeably with more targets than this in one file.
    public const MAX_TARGETS = 20;

    protected $fillable = ['ar_destination_id', 'title', 'sort_order'];

    public array $translatable = ['title'];

    protected $casts = [
        'target_order' => 'array',
        'targets_compiled_at' => 'datetime',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(ArDestination::class, 'ar_destination_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ArItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('mind_file')->useDisk('public')->singleFile();
    }

    /** Published items with a target image, in the order they go into the .mind file. */
    public function targetItems(): Collection
    {
        return $this->items()->published()->with('media')
            ->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (ArItem $item) => $item->hasMedia('target'))
            ->values();
    }

    public function isScannable(): bool
    {
        return filled($this->target_order) && $this->hasMedia('mind_file');
    }

    /** True when the compiled file no longer matches the zone's published items or their images. */
    public function needsCompile(): bool
    {
        if (! $this->isScannable() || ! $this->targets_compiled_at) {
            return true;
        }

        $items = $this->targetItems();

        return $items->pluck('id')->all() !== $this->target_order
            || $items->contains(fn (ArItem $item) => $item->getFirstMedia('target')->updated_at->gt($this->targets_compiled_at));
    }

    /** @param  list<int>  $itemIds  the order the browser compiled the targets in */
    public function storeCompiledTargets(UploadedFile $file, array $itemIds): void
    {
        $this->addMedia($file)->usingFileName('targets.mind')->toMediaCollection('mind_file');

        $this->forceFill([
            'target_order' => array_values(array_map('intval', $itemIds)),
            'targets_compiled_at' => now(),
        ])->save();
    }

    /**
     * One entry per compiled target index. Null for an item unpublished since the compile,
     * so the scanner ignores that image instead of showing hidden content.
     */
    public function scannerCards(): array
    {
        $items = $this->items()->published()->with('media')->get()->keyBy('id');
        $destinationSlug = $this->destination->slug;

        return collect($this->target_order ?? [])->map(function (int $id) use ($items, $destinationSlug) {
            $item = $items->get($id);

            return $item ? [
                'title' => $item->title,
                'summary' => $item->summary,
                'url' => route('ar.item', [$destinationSlug, $item->slug]),
                'audio' => $item->audioUrl(),
            ] : null;
        })->all();
    }
}

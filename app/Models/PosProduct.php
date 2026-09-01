<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosProduct extends Model
{
    use HasFactory, LogsActivity;

    // Every product needs a slug the moment it could be published online
    // (the shop route binds by slug), so a missing one is filled in from the
    // name here rather than relying on every creation path — admin form,
    // factory, import — to remember to set it.
    protected static function booted(): void
    {
        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = static::uniqueSlugFrom($product->name);
            }
        });
    }

    protected static function uniqueSlugFrom(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ++$suffix;
        }

        return $slug;
    }

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'sku',
        'barcode',
        'image',
        'gallery_images',
        'price',
        'tax_rate',
        'unit',
        'stock_quantity',
        'low_stock_threshold',
        'rack',
        'row',
        'position',
        'is_active',
        'is_published_online',
        'views_count',
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'gallery_images' => 'array',
        'is_published_online' => 'boolean',
        'views_count' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category_id', 'brand_id', 'name', 'name_ar', 'sku', 'barcode', 'price', 'tax_rate', 'unit', 'stock_quantity', 'low_stock_threshold', 'rack', 'row', 'position', 'is_active', 'is_published_online'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category()
    {
        return $this->belongsTo(PosProductCategory::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(PosBrand::class, 'brand_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(PosStockMovement::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(PosOrderItem::class, 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(PosProductReview::class, 'product_id');
    }

    public function approvedReviews()
    {
        return $this->reviews()->approved();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Gallery images for the online product detail page, always including
    // the primary `image` first so the storefront never renders an empty slider.
    public function getGalleryUrlsAttribute(): array
    {
        $urls = array_map(fn (string $path) => asset('storage/' . $path), $this->gallery_images ?? []);

        return $this->image ? array_unique([$this->image_url, ...$urls]) : $urls;
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->approvedReviews()->avg('rating'), 1);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    // Localized description, mirroring display_name's English fallback.
    public function getDisplayDescriptionAttribute(): ?string
    {
        if (app()->getLocale() === 'ar' && $this->description_ar) {
            return $this->description_ar;
        }

        return $this->description;
    }

    // Arabic name when one is set and the app is running in Arabic, English
    // (the always-present base column) otherwise.
    public function getDisplayNameAttribute(): string
    {
        if (app()->getLocale() === 'ar' && $this->name_ar) {
            return $this->name_ar;
        }

        return $this->name;
    }

    // "Rack A / Row 3 / Bin 12" style label, or null if no location is set at all.
    public function getLocationLabelAttribute(): ?string
    {
        $parts = array_filter([$this->rack, $this->row, $this->position]);

        return $parts ? implode(' / ', $parts) : null;
    }

    public function transactionItems()
    {
        return $this->morphMany(PosTransactionItem::class, 'itemable');
    }

    public function isLowStock(): bool
    {
        return $this->low_stock_threshold !== null && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return $this->is_active && $this->stock_quantity >= $quantity;
    }

    public function scopePublishedOnline(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_published_online', true);
    }

    // Applies a signed stock change and logs it. Positive for restock/return,
    // negative for sale — callers pass the correctly-signed quantity.
    public function applyStockMovement(string $type, int $signedQuantity, ?string $reference = null, ?int $userId = null): PosStockMovement
    {
        $this->increment('stock_quantity', $signedQuantity);

        return $this->stockMovements()->create([
            'type' => $type,
            'quantity' => $signedQuantity,
            'reference' => $reference,
            'user_id' => $userId,
        ]);
    }
}

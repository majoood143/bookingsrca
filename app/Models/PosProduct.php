<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosProduct extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'sku',
        'barcode',
        'image',
        'price',
        'tax_rate',
        'unit',
        'stock_quantity',
        'low_stock_threshold',
        'rack',
        'row',
        'position',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category_id', 'name', 'sku', 'barcode', 'price', 'tax_rate', 'unit', 'stock_quantity', 'low_stock_threshold', 'rack', 'row', 'position', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category()
    {
        return $this->belongsTo(PosProductCategory::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(PosStockMovement::class, 'product_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
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

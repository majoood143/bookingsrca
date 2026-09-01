<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosProductReview extends Model
{
    use LogsActivity;

    protected $fillable = [
        'product_id',
        'order_id',
        'customer_id',
        'rating',
        'title',
        'body',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rating', 'title', 'is_approved'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function product()
    {
        return $this->belongsTo(PosProduct::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class, 'customer_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    // A customer can only review a product they actually bought in a paid
    // order — checked before creating the review, not at the DB level.
    public static function eligibleOrderItem(PosOrder $order, int $productId): ?PosOrderItem
    {
        if (!$order->isPaid()) {
            return null;
        }

        return $order->items->firstWhere('product_id', $productId);
    }
}

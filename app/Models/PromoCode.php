<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use SoftDeletes;

    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
        'event_id',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_uses'       => 'integer',
        'used_count'     => 'integer',
        'is_active'      => 'boolean',
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the code is currently valid for the given event.
     *
     * @param int|null $eventId The event being booked (null skips the event check)
     */
    public function isValid(?int $eventId = null): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && now()->gt($this->valid_until)) {
            return false;
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }
        // Event-specific code: only valid for that event
        if ($this->event_id !== null && $eventId !== null && $this->event_id !== $eventId) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount on the given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round(($subtotal * (float) $this->discount_value) / 100, 2);
        }

        // Fixed: cannot exceed the subtotal
        return min((float) $this->discount_value, $subtotal);
    }

    /**
     * Human-readable discount label, e.g. "10%" or "5.000 OMR".
     */
    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return (float) $this->discount_value . '%';
        }

        return number_format((float) $this->discount_value, 3) . ' OMR';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ExtraService extends Model
{

    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity_available',
        'quantity_used',
        'is_active',
    ];

    protected $translatable = ['name', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['event_id', 'name', 'description', 'price', 'quantity_available', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_extra_services')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    // Helper methods

    // Mirrors TimeSlot::getActiveBookedQuantity() - counts confirmed bookings
    // plus pending ones still awaiting payment, computed live.
    public function getActiveUsedQuantity(): int
    {
        return (int) $this->bookings()
            ->where(function ($query) {
                $query->where('bookings.status', 'confirmed')
                    ->orWhere(function ($query) {
                        $query->where('bookings.status', 'pending')->where('bookings.payment_status', '!=', 'failed');
                    });
            })
            ->sum('booking_extra_services.quantity');
    }

    public function getRemainingQuantity()
    {
        if ($this->quantity_available === null) {
            return PHP_INT_MAX; // Unlimited
        }
        return max(0, $this->quantity_available - $this->getActiveUsedQuantity());
    }

    public function isAvailable($quantity = 1)
    {
        return $this->is_active && $this->getRemainingQuantity() >= $quantity;
    }
}

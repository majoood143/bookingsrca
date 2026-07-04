<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TimeSlot extends Model
{

    use HasFactory, LogsActivity;

    protected $fillable = [
        'event_id',
        'date',
        'start_time',
        'end_time',
        'label',
        'max_attendees',
        'current_bookings',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['event_id', 'date', 'start_time', 'end_time', 'max_attendees', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Helper methods

    // Quantity currently held against this slot: confirmed bookings plus pending
    // ones still awaiting payment. Computed live (not from the current_bookings
    // counter, which only updates on confirm()) so in-flight checkouts count too.
    public function getActiveBookedQuantity(): int
    {
        return (int) $this->bookings()
            ->where(function ($query) {
                $query->where('status', 'confirmed')
                    ->orWhere(function ($query) {
                        $query->where('status', 'pending')->where('payment_status', '!=', 'failed');
                    });
            })
            ->sum('quantity');
    }

    public function getRemainingCapacity()
    {
        return max(0, $this->max_attendees - $this->getActiveBookedQuantity());
    }

    public function isAvailable($quantity = 1)
    {
        return $this->is_active && $this->getRemainingCapacity() >= $quantity;
    }

    public function getTimeRange()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    // Admin-facing label for this slot (e.g. "Bus 2"), falling back to an
    // auto-numbered "Slot N" using the given position when none was set.
    // The custom label (free text) is shown as-is regardless of locale;
    // only the auto-numbered fallback is translated.
    public function displayLabel(int $ordinal, ?string $locale = null): string
    {
        return $this->label ?: __('signage.slot_fallback_label', ['number' => $ordinal], $locale);
    }
}

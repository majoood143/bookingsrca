<?php

namespace App\Models;

use Carbon\Carbon;
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
        'show_label_online',
        'show_label_kiosk',
        'max_attendees',
        'current_bookings',
        'is_active',
        'booking_channel',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['event_id', 'date', 'start_time', 'end_time', 'max_attendees', 'is_active', 'booking_channel'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'show_label_online' => 'boolean',
        'show_label_kiosk' => 'boolean',
    ];

    // MySQL's native DATE column silently truncates a full "Y-m-d H:i:s"
    // string on write, but SQLite has no real date type and stores it
    // verbatim — which then breaks equality lookups like
    // where('date', '2026-08-29') against a stored '2026-08-29 00:00:00'.
    // Normalize here so storage is identical across both drivers.
    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = $value ? Carbon::parse($value)->format('Y-m-d') : $value;
    }

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

    public function isBookableOnline(): bool
    {
        return in_array($this->booking_channel, ['both', 'online']);
    }

    public function isBookableAtKiosk(): bool
    {
        return in_array($this->booking_channel, ['both', 'kiosk']);
    }

    // Whether the custom `label` (e.g. "Bus 2") should be shown to customers
    // on the given booking flow. Only meaningful when a label was actually
    // set — the auto-numbered fallback is admin-facing only.
    public function showsLabelOnline(): bool
    {
        return filled($this->label) && $this->show_label_online;
    }

    public function showsLabelAtKiosk(): bool
    {
        return filled($this->label) && $this->show_label_kiosk;
    }

    // A slot is overnight when its end time is not strictly after its start
    // time (e.g. 23:30 - 00:30). Both columns are plain TIME values with no
    // date component, so this is purely a time-of-day comparison.
    public function isOvernight(): bool
    {
        return $this->end_time->format('H:i') <= $this->start_time->format('H:i');
    }

    // Real start instant, combining the slot's calendar `date` with its
    // time-of-day. Use this (not the bare `start_time` cast) for any
    // date-aware comparison, since `start_time` alone is always anchored to
    // "today" by the `datetime:H:i` cast.
    public function getStartDateTime(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->start_time->format('H:i:s'));
    }

    // Real end instant, rolled to the next calendar day when the slot is
    // overnight. This is the correct way to compute duration or to check
    // whether "now" still falls within the slot.
    public function getEndDateTime(): Carbon
    {
        $end = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time->format('H:i:s'));

        return $this->isOvernight() ? $end->addDay() : $end;
    }

    // End time formatted for display, with a "(next day)" indicator appended
    // when the slot rolls over midnight (e.g. "00:30 (next day)").
    public function getEndTimeLabel(): string
    {
        $label = $this->end_time->format('H:i');

        return $this->isOvernight() ? $label . ' ' . __('time_slot.suffix.next_day') : $label;
    }

    public function getTimeRange()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->getEndTimeLabel();
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

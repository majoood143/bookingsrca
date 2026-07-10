<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'location',
        'organizer',
        'start_date',
        'end_date',
        'is_recurring',
        'recurring_days',
        'image',
        'status',
        'password',
        'max_attendees',
    ];

    protected $hidden = ['password'];

    protected $translatable = ['title', 'description', 'location', 'organizer'];

    protected $casts = [
        'recurring_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'description', 'location', 'organizer',
                'status', 'start_date', 'end_date', 'max_attendees',
                'is_recurring', 'recurring_days',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->getTranslation('title', 'en'));
            }
        });
    }

    // Relationships
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function extraServices()
    {
        return $this->hasMany(ExtraService::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function signageSetting()
    {
        return $this->hasOne(EventSignageSetting::class);
    }

    public function kiosks()
    {
        return $this->hasMany(Kiosk::class);
    }

    // Returns the configured signage settings for this event, or an
    // unsaved instance carrying the column defaults so the signage
    // dashboard always has values to render even before an admin
    // configures anything.
    public function signageSettingOrDefault(): EventSignageSetting
    {
        return $this->signageSetting ?? new EventSignageSetting([
            'early_arrival_minutes' => 5,
            'gathering_alert_minutes' => 5,
            'ready_threshold_minutes' => 15,
            'soon_threshold_minutes' => 60,
            'upcoming_trips_count' => 4,
            'language_switch_seconds' => 10,
            'is_enabled' => true,
        ]);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now()->format('Y-m-d'));
    }

    // Access control helpers
    public function isPrivate(): bool
    {
        return $this->status === 'private';
    }

    public function isGuestVisible(): bool
    {
        return in_array($this->status, ['published', 'private'], true);
    }

    public function checkPassword(?string $value): bool
    {
        return $this->isPrivate()
            && filled($this->password)
            && filled($value)
            && hash_equals($this->password, $value);
    }

    // Helper methods
    public function getAvailableDates()
    {
        $dates = [];
        $today = now()->startOfDay();
        $current = $this->start_date->copy();

        if ($current->lt($today)) {
            $current = $today->copy();
        }

        while ($current->lte($this->end_date)) {
            if ($this->is_recurring) {
                $dayName = strtolower($current->format('l'));
                if (in_array($dayName, $this->recurring_days ?? [])) {
                    $dates[] = $current->format('Y-m-d');
                }
            } else {
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $dates;
    }

    // Dates within getAvailableDates() that also have at least one active,
    // generated TimeSlot whose start datetime hasn't already passed.
    public function getBookableDates()
    {
        $now = now();

        $slotDates = $this->timeSlots()
            ->where('is_active', true)
            ->get()
            ->filter(function ($slot) use ($now) {
                $slotStart = \Carbon\Carbon::parse(
                    $slot->date->format('Y-m-d') . ' ' . $slot->start_time->format('H:i:s')
                );
                return $slotStart->gt($now);
            })
            ->map(fn($slot) => $slot->date->format('Y-m-d'))
            ->unique();

        return array_values(array_intersect($this->getAvailableDates(), $slotDates->all()));
    }

    // Dates whose active, future time slots exist but are all fully booked
    // (zero remaining capacity) — the date still shows on the calendar, but
    // there is nothing left to sell for it.
    public function getSoldOutDates(): array
    {
        $now = now();

        return $this->timeSlots()
            ->where('is_active', true)
            ->get()
            ->filter(function ($slot) use ($now) {
                $slotStart = \Carbon\Carbon::parse(
                    $slot->date->format('Y-m-d') . ' ' . $slot->start_time->format('H:i:s')
                );
                return $slotStart->gt($now);
            })
            ->groupBy(fn ($slot) => $slot->date->format('Y-m-d'))
            ->filter(fn ($slots) => $slots->every(fn ($slot) => !$slot->isAvailable()))
            ->keys()
            ->all();
    }

    public function getTotalBookings()
    {
        return $this->bookings()->where('status', '!=', 'cancelled')->sum('quantity');
    }

    public function getRemainingCapacity()
    {
        return max(0, $this->max_attendees - $this->getTotalBookings());
    }
}


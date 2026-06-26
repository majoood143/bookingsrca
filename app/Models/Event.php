<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;

class Event extends Model
{
    //
    use HasFactory, HasTranslations;

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
        'max_attendees',
    ];

    protected $translatable = ['title', 'description', 'location'];

    protected $casts = [
        'recurring_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

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

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now()->format('Y-m-d'));
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
    // generated TimeSlot — i.e. dates customers can actually book.
    public function getBookableDates()
    {
        $slotDates = $this->timeSlots()
            ->where('is_active', true)
            ->get()
            ->map(fn($slot) => $slot->date->format('Y-m-d'))
            ->unique();

        return array_values(array_intersect($this->getAvailableDates(), $slotDates->all()));
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


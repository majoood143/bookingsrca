<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
class TicketType extends Model
{

    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity_available',
        'quantity_sold',
        'sale_start_date',
        'sale_end_date',
        'is_active',
    ];

    protected $translatable = ['name', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['event_id', 'name', 'price', 'quantity_available', 'sale_start_date', 'sale_end_date', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'price' => 'decimal:2',
        'sale_start_date' => 'date',
        'sale_end_date' => 'date',
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

    public function dependsOnMany()
    {
        return $this->belongsToMany(
            TicketType::class,
            'ticket_type_dependencies',
            'ticket_type_id',
            'depends_on_ticket_type_id'
        );
    }

    public function dependents()
    {
        return $this->belongsToMany(
            TicketType::class,
            'ticket_type_dependencies',
            'depends_on_ticket_type_id',
            'ticket_type_id'
        );
    }

    // Helper methods

    // Mirrors TimeSlot::getActiveBookedQuantity() - counts confirmed bookings
    // plus pending ones still awaiting payment, computed live.
    public function getActiveSoldQuantity(): int
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

    public function getRemainingQuantity()
    {
        return max(0, $this->quantity_available - $this->getActiveSoldQuantity());
    }

    public function isAvailable($quantity = 1)
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->format('Y-m-d');

        if ($this->sale_start_date && $now < $this->sale_start_date->format('Y-m-d')) {
            return false;
        }

        if ($this->sale_end_date && $now > $this->sale_end_date->format('Y-m-d')) {
            return false;
        }

        return $this->getRemainingQuantity() >= $quantity;
    }
}

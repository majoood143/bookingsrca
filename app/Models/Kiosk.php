<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Kiosk extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'event_id',
        'is_active',
        'idle_timeout_seconds',
        'enabled_payment_methods',
        'receipt_footer_text',
        'slideshow_images',
        'reader_connected',
        'reader_last_seen_at',
        'printer_connected',
        'printer_last_seen_at',
        'app_version',
        'override_booking_rules',
        'min_tickets_per_booking',
        'max_tickets_per_booking',
        'max_attendee_age_years',
        'pending_booking_expiry_minutes',
        'show_slot_end_time',
    ];

    protected $translatable = ['receipt_footer_text'];

    protected $casts = [
        'is_active' => 'boolean',
        'idle_timeout_seconds' => 'integer',
        'enabled_payment_methods' => 'array',
        'slideshow_images' => 'array',
        'reader_connected' => 'boolean',
        'reader_last_seen_at' => 'datetime',
        'printer_connected' => 'boolean',
        'printer_last_seen_at' => 'datetime',
        'override_booking_rules' => 'boolean',
        'min_tickets_per_booking' => 'integer',
        'max_tickets_per_booking' => 'integer',
        'max_attendee_age_years' => 'integer',
        'pending_booking_expiry_minutes' => 'integer',
        'show_slot_end_time' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'event_id', 'is_active', 'idle_timeout_seconds', 'enabled_payment_methods', 'override_booking_rules', 'min_tickets_per_booking', 'max_tickets_per_booking', 'max_attendee_age_years', 'pending_booking_expiry_minutes', 'show_slot_end_time'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getRouteKeyName(): string
    {
        return 'code';
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

    public function cardTransactions()
    {
        return $this->hasMany(KioskCardTransaction::class);
    }

    // Helpers
    public function supportsPaymentMethod(string $method): bool
    {
        return in_array($method, $this->enabled_payment_methods ?? [], true);
    }

    // Per-kiosk replacement for the general settings "Booking Rules" — mirrors
    // Event::fieldVisibilityOverridesFor(): null means "use the global
    // BookingSetting values", a non-null array fully replaces them so this
    // kiosk's bookings are governed by its own rules instead.
    public function bookingRuleOverrides(): ?array
    {
        if (!$this->override_booking_rules) {
            return null;
        }

        return [
            'min_tickets_per_booking' => $this->min_tickets_per_booking,
            'max_tickets_per_booking' => $this->max_tickets_per_booking,
            'max_attendee_age_years' => $this->max_attendee_age_years,
            'pending_booking_expiry_minutes' => $this->pending_booking_expiry_minutes,
            'show_slot_end_time' => $this->show_slot_end_time,
        ];
    }

    public function recordHeartbeat(bool $readerConnected, bool $printerConnected, ?string $appVersion = null): void
    {
        $this->update([
            'reader_connected' => $readerConnected,
            'reader_last_seen_at' => now(),
            'printer_connected' => $printerConnected,
            'printer_last_seen_at' => now(),
            'app_version' => $appVersion ?? $this->app_version,
        ]);
    }
}

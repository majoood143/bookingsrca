<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Kiosk extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'device_token',
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
        'override_min_tickets_per_booking',
        'min_tickets_per_booking',
        'override_max_tickets_per_booking',
        'max_tickets_per_booking',
        'override_max_attendee_age_years',
        'max_attendee_age_years',
        'override_pending_booking_expiry_minutes',
        'pending_booking_expiry_minutes',
        'override_show_slot_end_time',
        'show_slot_end_time',
        'override_booking_cutoff',
        'booking_cutoff_enabled',
        'booking_cutoff_value',
        'booking_cutoff_unit',
        'override_capacity_enforcement',
        'enforce_capacity',
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
        'override_min_tickets_per_booking' => 'boolean',
        'min_tickets_per_booking' => 'integer',
        'override_max_tickets_per_booking' => 'boolean',
        'max_tickets_per_booking' => 'integer',
        'override_max_attendee_age_years' => 'boolean',
        'max_attendee_age_years' => 'integer',
        'override_pending_booking_expiry_minutes' => 'boolean',
        'pending_booking_expiry_minutes' => 'integer',
        'override_show_slot_end_time' => 'boolean',
        'show_slot_end_time' => 'boolean',
        'override_booking_cutoff' => 'boolean',
        'booking_cutoff_enabled' => 'boolean',
        'booking_cutoff_value' => 'integer',
        'override_capacity_enforcement' => 'boolean',
        'enforce_capacity' => 'boolean',
    ];

    // True when this kiosk has no device token (open, as before) or the given
    // token matches it.
    public function acceptsDeviceToken(?string $token): bool
    {
        return blank($this->device_token)
            || (filled($token) && hash_equals($this->device_token, $token));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'code', 'event_id', 'is_active', 'idle_timeout_seconds', 'enabled_payment_methods',
                'override_min_tickets_per_booking', 'min_tickets_per_booking',
                'override_max_tickets_per_booking', 'max_tickets_per_booking',
                'override_max_attendee_age_years', 'max_attendee_age_years',
                'override_pending_booking_expiry_minutes', 'pending_booking_expiry_minutes',
                'override_show_slot_end_time', 'show_slot_end_time',
                'override_booking_cutoff', 'booking_cutoff_enabled', 'booking_cutoff_value', 'booking_cutoff_unit',
                'override_capacity_enforcement', 'enforce_capacity',
            ])
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

    // Per-kiosk, per-rule replacement for the general settings "Booking
    // Rules": each rule has its own override toggle, so a kiosk can replace
    // just one value (e.g. its own pending-expiry window) while inheriting
    // the rest from the global BookingSetting values. Only overridden keys
    // are present in the returned array.
    public function bookingRuleOverrides(): array
    {
        $overrides = [];

        if ($this->override_min_tickets_per_booking) {
            $overrides['min_tickets_per_booking'] = $this->min_tickets_per_booking;
        }

        if ($this->override_max_tickets_per_booking) {
            $overrides['max_tickets_per_booking'] = $this->max_tickets_per_booking;
        }

        if ($this->override_max_attendee_age_years) {
            $overrides['max_attendee_age_years'] = $this->max_attendee_age_years;
        }

        if ($this->override_pending_booking_expiry_minutes) {
            $overrides['pending_booking_expiry_minutes'] = $this->pending_booking_expiry_minutes;
        }

        if ($this->override_show_slot_end_time) {
            $overrides['show_slot_end_time'] = $this->show_slot_end_time;
        }

        return $overrides;
    }

    // Per-kiosk booking-cutoff window, replacing the event's own cutoff for
    // bookings made on this device — see Event::cutoffMinutesFor()/cutoffClosesAtFor().
    public function bookingCutoffOverride(): ?array
    {
        if (! $this->override_booking_cutoff) {
            return null;
        }

        return [
            'enabled' => $this->booking_cutoff_enabled,
            'value' => $this->booking_cutoff_value,
            'unit' => $this->booking_cutoff_unit,
        ];
    }

    // Per-kiosk capacity-enforcement override — null means "use the calling
    // context's default" (kiosks never enforce capacity unless this is set).
    public function enforcesCapacityOverride(): ?bool
    {
        if (! $this->override_capacity_enforcement) {
            return null;
        }

        return (bool) $this->enforce_capacity;
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

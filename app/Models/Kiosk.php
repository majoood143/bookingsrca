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
        'reader_connected',
        'reader_last_seen_at',
        'printer_connected',
        'printer_last_seen_at',
        'app_version',
    ];

    protected $translatable = ['receipt_footer_text'];

    protected $casts = [
        'is_active' => 'boolean',
        'idle_timeout_seconds' => 'integer',
        'enabled_payment_methods' => 'array',
        'reader_connected' => 'boolean',
        'reader_last_seen_at' => 'datetime',
        'printer_connected' => 'boolean',
        'printer_last_seen_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'event_id', 'is_active', 'idle_timeout_seconds', 'enabled_payment_methods'])
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

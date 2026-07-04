<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BookingSetting extends Model
{
    use LogsActivity;

    protected $fillable = ['key', 'value', 'type', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value', 'type', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get a setting value by key with caching
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("booking_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match($setting->type) {
                'number' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                default => $setting->value,
            };
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("booking_setting_{$key}");
    }

    public static function clearCache(): void
    {
        foreach (self::getCacheKeys() as $key) {
            Cache::forget("booking_setting_{$key}");
        }
    }

    private static function getCacheKeys(): array
    {
        return [
            'max_tickets_per_booking',
            'min_tickets_per_booking',
            'show_email',
            'show_phone',
            'show_date_of_birth',
            'show_gender',
            'show_nationality',
            'show_identity_number',
            'max_attendee_age_years',
            'terms_en',
            'terms_ar',
            'active_gateway',
            'pending_booking_expiry_minutes',
            'site_name_en',
            'site_name_ar',
            'site_logo',
            'primary_color',
            'secondary_color',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CmsSetting extends Model
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
        return Cache::remember("cms_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
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

        Cache::forget("cms_setting_{$key}");
    }

    public static function clearCache(): void
    {
        foreach (self::getCacheKeys() as $key) {
            Cache::forget("cms_setting_{$key}");
        }
    }

    private static function getCacheKeys(): array
    {
        return [
            'default_meta_description',
            'default_og_image',
            'posts_per_page',
            'sitemap_enabled',
            'rss_enabled',
            'robots_block_all',
        ];
    }
}

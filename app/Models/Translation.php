<?php

namespace App\Models;

use App\Support\DatabaseTranslationLoader;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * An admin-managed override for a key that otherwise comes from the lang files.
 * Read back by DatabaseTranslationLoader, so every __() call picks it up.
 */
class Translation extends Model
{
    use LogsActivity;

    public const CACHE_KEY = 'translation_overrides';

    protected $fillable = ['group', 'key', 'text'];

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['group', 'key', 'text'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Locale => text. Blank locales are dropped so they fall back to the lang file.
     */
    protected function text(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode($value, true) : [],
            set: fn ($value) => json_encode(
                array_filter((array) $value, fn ($text) => filled($text)),
                JSON_UNESCAPED_UNICODE,
            ),
        );
    }

    /**
     * Every override as [locale => [group => [key => text]]], cached until an edit.
     */
    public static function overrides(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $overrides = [];

            foreach (static::query()->get(['group', 'key', 'text']) as $row) {
                foreach ($row->text as $locale => $text) {
                    $overrides[$locale][$row->group][$row->key] = $text;
                }
            }

            return $overrides;
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);

        $loader = app('translation.loader');

        if ($loader instanceof DatabaseTranslationLoader) {
            $loader->flush();
        }

        // Groups already read during this request would otherwise keep the old text.
        app('translator')->setLoaded([]);
    }
}

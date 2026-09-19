<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\FileLoader;

/**
 * Every translatable key found in the lang files, with its static text.
 * Read straight from the files, so it never includes admin overrides.
 */
class TranslationCatalog
{
    public const JSON_GROUP = '*';

    protected static ?array $entries = null;

    /**
     * Search the keys as [id => label], for a select.
     */
    public static function options(string $search = '', int $limit = 50): array
    {
        $search = mb_strtolower(trim($search));

        return collect(static::entries())
            ->filter(fn (array $entry, string $id) => $search === ''
                || str_contains(mb_strtolower($id), $search)
                || str_contains(mb_strtolower((string) $entry['en']), $search)
                || str_contains((string) $entry['ar'], $search))
            ->take($limit)
            ->map(fn (array $entry) => static::label($entry['group'], $entry['key']))
            ->all();
    }

    public static function id(string $group, string $key): string
    {
        return "{$group}|{$key}";
    }

    /**
     * @return array{0: string, 1: string} [group, key]
     */
    public static function split(string $id): array
    {
        return explode('|', $id, 2) + [1 => ''];
    }

    public static function label(string $group, string $key): string
    {
        return $group === self::JSON_GROUP ? $key : "{$group}.{$key}";
    }

    public static function find(?string $group, ?string $key): ?array
    {
        return static::entries()[static::id((string) $group, (string) $key)] ?? null;
    }

    public static function default(?string $group, ?string $key, string $locale): ?string
    {
        return static::find($group, $key)[$locale] ?? null;
    }

    public static function groups(): array
    {
        return collect(static::entries())->pluck('group')->unique()->sort()->values()->all();
    }

    public static function flush(): void
    {
        static::$entries = null;
    }

    /**
     * @return array<string, array{group: string, key: string, en: ?string, ar: ?string}>
     */
    protected static function entries(): array
    {
        if (static::$entries !== null) {
            return static::$entries;
        }

        $files = new FileLoader(new Filesystem, lang_path());
        $entries = [];

        foreach (['en', 'ar'] as $locale) {
            foreach (static::groupsIn($locale) as $group) {
                $lines = $group === self::JSON_GROUP
                    ? $files->load($locale, '*', '*')
                    : Arr::dot($files->load($locale, $group));

                foreach ($lines as $key => $text) {
                    if (! is_string($text)) {
                        continue;
                    }

                    $id = static::id($group, (string) $key);
                    $entries[$id] ??= ['group' => $group, 'key' => (string) $key, 'en' => null, 'ar' => null];
                    $entries[$id][$locale] = $text;
                }
            }
        }

        ksort($entries);

        return static::$entries = $entries;
    }

    protected static function groupsIn(string $locale): array
    {
        $groups = collect(glob(lang_path("{$locale}/*.php")) ?: [])
            ->map(fn (string $path) => basename($path, '.php'));

        return $groups->prepend(self::JSON_GROUP)->all();
    }
}

<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\Field;

/** One form field per app language (config/translatable.php), so adding a language needs no form changes. */
final class Localized
{
    /**
     * @param  Closure(string $locale, bool $isDefault): Field  $make
     * @return list<Field>
     */
    public static function fields(Closure $make): array
    {
        $default = config('translatable.fallback_locale');

        return array_map(
            fn (string $locale) => $make($locale, $locale === $default)
                ->extraInputAttributes(['dir' => in_array($locale, ['ar', 'fa', 'ur', 'he'], true) ? 'rtl' : 'ltr']),
            config('translatable.locales'),
        );
    }
}

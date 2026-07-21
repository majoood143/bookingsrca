<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserPrefix: string implements HasLabel
{
    case Mr = 'mr';
    case Mrs = 'mrs';
    case Ms = 'ms';
    case Miss = 'miss';
    case Dr = 'dr';
    case Prof = 'prof';
    case Eng = 'eng';

    public function getLabel(): string
    {
        return __('user.prefixes.' . $this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn ($case) => $case->getLabel(), self::cases())
        );
    }
}

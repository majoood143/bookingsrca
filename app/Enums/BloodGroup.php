<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BloodGroup: string implements HasLabel
{
    case APositive = 'a+';
    case ANegative = 'a-';
    case BPositive = 'b+';
    case BNegative = 'b-';
    case ABPositive = 'ab+';
    case ABNegative = 'ab-';
    case OPositive = 'o+';
    case ONegative = 'o-';

    public function getLabel(): string
    {
        return __('user.blood_groups.' . $this->value);
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

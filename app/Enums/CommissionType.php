<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommissionType: string implements HasLabel, HasColor
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return __('commission_setting.types.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Percentage => 'success',
            self::Fixed => 'info',
        };
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

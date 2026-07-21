<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RecurringFrequency: string implements HasLabel
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function getLabel(): string
    {
        return __('expense.recurring_frequencies.' . $this->value);
    }

    public function getCarbonInterval(): string
    {
        return match ($this) {
            self::Daily => '1 day',
            self::Weekly => '1 week',
            self::Monthly => '1 month',
            self::Quarterly => '3 months',
            self::Yearly => '1 year',
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

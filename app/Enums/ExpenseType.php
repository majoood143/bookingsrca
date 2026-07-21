<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpenseType: string implements HasLabel, HasColor, HasIcon
{
    case Event = 'event';
    case Operational = 'operational';
    case Recurring = 'recurring';
    case OneTime = 'one_time';

    public function getLabel(): string
    {
        return __('expense.types.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Event => 'info',
            self::Operational => 'warning',
            self::Recurring => 'success',
            self::OneTime => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Event => 'heroicon-o-calendar-days',
            self::Operational => 'heroicon-o-cog-6-tooth',
            self::Recurring => 'heroicon-o-arrow-path',
            self::OneTime => 'heroicon-o-bolt',
        };
    }

    public function getDescription(): string
    {
        return __('expense.type_descriptions.' . $this->value);
    }

    public function canLinkToBooking(): bool
    {
        return $this === self::Event;
    }

    public function supportsRecurring(): bool
    {
        return $this === self::Recurring;
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

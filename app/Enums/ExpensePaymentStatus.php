<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpensePaymentStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Partial = 'partial';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('expense.payment_statuses.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Partial => 'info',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Paid => 'heroicon-o-check-badge',
            self::Partial => 'heroicon-o-minus-circle',
            self::Cancelled => 'heroicon-o-x-mark',
        };
    }

    public function requiresAttention(): bool
    {
        return in_array($this, [self::Pending, self::Partial]);
    }

    public function isComplete(): bool
    {
        return $this === self::Paid;
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

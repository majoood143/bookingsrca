<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpensePaymentMethod: string implements HasLabel, HasColor, HasIcon
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Card = 'card';
    case Cheque = 'cheque';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('expense.payment_methods.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::BankTransfer => 'info',
            self::Card => 'primary',
            self::Cheque => 'warning',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'heroicon-o-banknotes',
            self::BankTransfer => 'heroicon-o-building-library',
            self::Card => 'heroicon-o-credit-card',
            self::Cheque => 'heroicon-o-document-text',
            self::Other => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this, [self::BankTransfer, self::Cheque, self::Card]);
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

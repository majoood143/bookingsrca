<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpenseStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return __('expense.statuses.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Archived => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Submitted => 'heroicon-o-clock',
            self::Approved => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Archived => 'heroicon-o-archive-box',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Rejected]);
    }

    public function countsInReports(): bool
    {
        return $this === self::Approved;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Draft => in_array($newStatus, [self::Submitted, self::Approved]),
            self::Submitted => in_array($newStatus, [self::Approved, self::Rejected]),
            self::Approved => $newStatus === self::Archived,
            self::Rejected => in_array($newStatus, [self::Draft, self::Submitted]),
            self::Archived => false,
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

    /**
     * @return array<self>
     */
    public static function reportableStatuses(): array
    {
        return [self::Approved];
    }
}

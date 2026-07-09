<?php

namespace App\Filament\Resources\KioskCardResource\Pages;

use App\Filament\Resources\KioskCardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKioskCard extends CreateRecord
{
    protected static string $resource = KioskCardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('kiosk_card.notifications.created');
    }

    // Log the starting balance as the card's first ledger entry, so the
    // transactions relation always accounts for the full balance.
    protected function afterCreate(): void
    {
        $balance = (float) $this->record->balance;

        if ($balance > 0) {
            $this->record->transactions()->create([
                'type'          => 'adjustment',
                'amount'        => $balance,
                'balance_after' => $balance,
                'recorded_by'   => auth()->id(),
                'reference'     => 'Initial balance',
            ]);
        }
    }
}

<?php

namespace App\Filament\Resources\KioskCardResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\KioskCardResource;
use Filament\Resources\Pages\EditRecord;

class EditKioskCard extends EditRecord
{
    protected static string $resource = KioskCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('kiosk_card.notifications.updated');
    }
}

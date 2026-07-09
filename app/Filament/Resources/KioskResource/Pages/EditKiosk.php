<?php

namespace App\Filament\Resources\KioskResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\KioskResource;
use Filament\Resources\Pages\EditRecord;

class EditKiosk extends EditRecord
{
    protected static string $resource = KioskResource::class;

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
        return __('kiosk.notifications.updated');
    }
}

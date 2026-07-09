<?php

namespace App\Filament\Resources\KioskResource\Pages;

use App\Filament\Resources\KioskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKiosk extends CreateRecord
{
    protected static string $resource = KioskResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('kiosk.notifications.created');
    }
}

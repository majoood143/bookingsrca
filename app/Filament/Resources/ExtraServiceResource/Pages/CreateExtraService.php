<?php

namespace App\Filament\Resources\ExtraServiceResource\Pages;

use App\Filament\Resources\ExtraServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExtraService extends CreateRecord
{
    protected static string $resource = ExtraServiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('extra_service.notifications.created');
    }
}
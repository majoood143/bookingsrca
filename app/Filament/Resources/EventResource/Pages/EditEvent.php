<?php

namespace App\Filament\Resources\EventResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Pages\ViewEvent;
use App\Filament\Resources\EventResource\Pages\ListEventActivities;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewEvent::class,
            self::class,
            ListEventActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('event.notifications.updated');
    }
}
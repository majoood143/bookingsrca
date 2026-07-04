<?php

namespace App\Filament\Resources\ExtraServiceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\ExtraServiceResource;
use App\Filament\Resources\ExtraServiceResource\Pages\ListExtraServiceActivities;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExtraService extends EditRecord
{
    protected static string $resource = ExtraServiceResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            ListExtraServiceActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    if ($this->record->bookings()->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title(__('extra_service.notifications.cannot_delete'))
                            ->body(__('extra_service.notifications.has_bookings'))
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('extra_service.notifications.updated');
    }
}

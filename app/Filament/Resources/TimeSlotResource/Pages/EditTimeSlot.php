<?php

namespace App\Filament\Resources\TimeSlotResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\TimeSlotResource;
use App\Filament\Resources\TimeSlotResource\Pages\ListTimeSlotActivities;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeSlot extends EditRecord
{
    protected static string $resource = TimeSlotResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            ListTimeSlotActivities::class,
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
                            ->title(__('time_slot.notifications.cannot_delete'))
                            ->body(__('time_slot.notifications.has_bookings'))
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
        return __('time_slot.notifications.updated');
    }
}

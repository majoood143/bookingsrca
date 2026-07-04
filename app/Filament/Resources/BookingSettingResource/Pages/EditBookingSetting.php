<?php

namespace App\Filament\Resources\BookingSettingResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\BookingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BookingSettingResource\Pages\ListBookingSettingActivities;
use App\Models\BookingSetting;

class EditBookingSetting extends EditRecord
{
    protected static string $resource = BookingSettingResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            ListBookingSettingActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        BookingSetting::clearCache();

        Notification::make()
            ->success()
            ->title(__('booking_setting.notifications.updated'))
            ->body(__('booking_setting.notifications.updated_body'))
            ->send();
    }
}

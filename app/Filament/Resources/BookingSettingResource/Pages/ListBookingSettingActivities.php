<?php

namespace App\Filament\Resources\BookingSettingResource\Pages;

use App\Filament\Resources\BookingSettingResource;
use App\Filament\Resources\BookingSettingResource\Pages\EditBookingSetting;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListBookingSettingActivities extends ListActivities
{
    protected static string $resource = BookingSettingResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            EditBookingSetting::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

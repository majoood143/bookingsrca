<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\BookingResource\Pages\ViewBooking;
use App\Filament\Resources\BookingResource\Pages\EditBooking;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListBookingActivities extends ListActivities
{
    protected static string $resource = BookingResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewBooking::class,
            EditBooking::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

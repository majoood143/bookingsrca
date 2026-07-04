<?php

namespace App\Filament\Resources\BookingAttendeeResource\Pages;

use App\Filament\Resources\BookingAttendeeResource;
use App\Filament\Resources\BookingAttendeeResource\Pages\ViewBookingAttendee;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListBookingAttendeeActivities extends ListActivities
{
    protected static string $resource = BookingAttendeeResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewBookingAttendee::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

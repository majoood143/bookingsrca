<?php

namespace App\Filament\Resources\TimeSlotResource\Pages;

use App\Filament\Resources\TimeSlotResource;
use App\Filament\Resources\TimeSlotResource\Pages\EditTimeSlot;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListTimeSlotActivities extends ListActivities
{
    protected static string $resource = TimeSlotResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            EditTimeSlot::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

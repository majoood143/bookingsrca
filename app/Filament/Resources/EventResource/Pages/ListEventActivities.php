<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Pages\ViewEvent;
use App\Filament\Resources\EventResource\Pages\EditEvent;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListEventActivities extends ListActivities
{
    protected static string $resource = EventResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewEvent::class,
            EditEvent::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

<?php

namespace App\Filament\Resources\TicketTypeResource\Pages;

use App\Filament\Resources\TicketTypeResource;
use App\Filament\Resources\TicketTypeResource\Pages\EditTicketType;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListTicketTypeActivities extends ListActivities
{
    protected static string $resource = TicketTypeResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            EditTicketType::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

<?php

namespace App\Filament\Resources\ExtraServiceResource\Pages;

use App\Filament\Resources\ExtraServiceResource;
use App\Filament\Resources\ExtraServiceResource\Pages\EditExtraService;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListExtraServiceActivities extends ListActivities
{
    protected static string $resource = ExtraServiceResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            EditExtraService::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

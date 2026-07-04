<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\ViewRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListRoleActivities extends ListActivities
{
    protected static string $resource = RoleResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewRole::class,
            EditRole::class,
            self::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }
}

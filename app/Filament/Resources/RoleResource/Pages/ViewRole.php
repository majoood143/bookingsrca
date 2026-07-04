<?php

namespace App\Filament\Resources\RoleResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Pages\ListRoleActivities;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            EditRole::class,
            ListRoleActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

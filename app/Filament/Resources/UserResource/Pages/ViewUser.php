<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUserActivities;
use App\Filament\Resources\UserResource\Widgets\UserStatsOverview;
use App\Filament\Resources\UserResource\Widgets\UserTicketBreakdown;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            EditUser::class,
            ListUserActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            UserStatsOverview::class,
            UserTicketBreakdown::class,
        ];
    }
}

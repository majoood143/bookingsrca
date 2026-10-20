<?php

namespace App\Filament\Resources\GamePlayerResource\Pages;

use App\Filament\Resources\GamePlayerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewGamePlayer extends ViewRecord
{
    protected static string $resource = GamePlayerResource::class;

    protected function getHeaderActions(): array
    {
        return GamePlayerResource::blockActions();
    }
}

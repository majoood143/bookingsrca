<?php

namespace App\Filament\Resources\GameAttemptResource\Pages;

use App\Filament\Resources\GameAttemptResource;
use Filament\Resources\Pages\ViewRecord;

class ViewGameAttempt extends ViewRecord
{
    protected static string $resource = GameAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return GameAttemptResource::reviewActions();
    }
}

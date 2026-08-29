<?php

namespace App\Filament\Resources\PosTerminalResource\Pages;

use App\Filament\Resources\PosTerminalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosTerminals extends ListRecords
{
    protected static string $resource = PosTerminalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ArItemResource\Pages;

use App\Filament\Resources\ArItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArItems extends ListRecords
{
    protected static string $resource = ArItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

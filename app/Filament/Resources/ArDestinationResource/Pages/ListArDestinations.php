<?php

namespace App\Filament\Resources\ArDestinationResource\Pages;

use App\Filament\Resources\ArDestinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArDestinations extends ListRecords
{
    protected static string $resource = ArDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

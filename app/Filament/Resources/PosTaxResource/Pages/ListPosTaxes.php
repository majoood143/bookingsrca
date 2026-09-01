<?php

namespace App\Filament\Resources\PosTaxResource\Pages;

use App\Filament\Resources\PosTaxResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosTaxes extends ListRecords
{
    protected static string $resource = PosTaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PosBrandResource\Pages;

use App\Filament\Resources\PosBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosBrands extends ListRecords
{
    protected static string $resource = PosBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

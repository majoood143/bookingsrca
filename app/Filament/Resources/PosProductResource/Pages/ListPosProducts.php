<?php

namespace App\Filament\Resources\PosProductResource\Pages;

use App\Filament\Resources\PosProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosProducts extends ListRecords
{
    protected static string $resource = PosProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

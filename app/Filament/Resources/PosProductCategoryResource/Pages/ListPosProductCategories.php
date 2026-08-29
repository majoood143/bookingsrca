<?php

namespace App\Filament\Resources\PosProductCategoryResource\Pages;

use App\Filament\Resources\PosProductCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosProductCategories extends ListRecords
{
    protected static string $resource = PosProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

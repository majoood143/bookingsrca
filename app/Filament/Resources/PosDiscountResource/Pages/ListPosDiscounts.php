<?php

namespace App\Filament\Resources\PosDiscountResource\Pages;

use App\Filament\Resources\PosDiscountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosDiscounts extends ListRecords
{
    protected static string $resource = PosDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PosCustomerResource\Pages;

use App\Filament\Resources\PosCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosCustomers extends ListRecords
{
    protected static string $resource = PosCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

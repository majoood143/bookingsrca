<?php

namespace App\Filament\Resources\PosCustomerResource\Pages;

use App\Filament\Resources\PosCustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosCustomer extends CreateRecord
{
    protected static string $resource = PosCustomerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

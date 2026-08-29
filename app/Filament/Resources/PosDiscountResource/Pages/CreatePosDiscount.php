<?php

namespace App\Filament\Resources\PosDiscountResource\Pages;

use App\Filament\Resources\PosDiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosDiscount extends CreateRecord
{
    protected static string $resource = PosDiscountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

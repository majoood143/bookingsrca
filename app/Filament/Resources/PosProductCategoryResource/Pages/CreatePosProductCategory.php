<?php

namespace App\Filament\Resources\PosProductCategoryResource\Pages;

use App\Filament\Resources\PosProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosProductCategory extends CreateRecord
{
    protected static string $resource = PosProductCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

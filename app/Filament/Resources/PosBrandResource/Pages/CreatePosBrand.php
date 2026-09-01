<?php

namespace App\Filament\Resources\PosBrandResource\Pages;

use App\Filament\Resources\PosBrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosBrand extends CreateRecord
{
    protected static string $resource = PosBrandResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

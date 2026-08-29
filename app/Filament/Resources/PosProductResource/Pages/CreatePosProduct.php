<?php

namespace App\Filament\Resources\PosProductResource\Pages;

use App\Filament\Resources\PosProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosProduct extends CreateRecord
{
    protected static string $resource = PosProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

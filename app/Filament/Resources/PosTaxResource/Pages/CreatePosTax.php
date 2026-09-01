<?php

namespace App\Filament\Resources\PosTaxResource\Pages;

use App\Filament\Resources\PosTaxResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosTax extends CreateRecord
{
    protected static string $resource = PosTaxResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

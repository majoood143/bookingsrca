<?php

namespace App\Filament\Resources\ArDestinationResource\Pages;

use App\Filament\Resources\ArDestinationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArDestination extends CreateRecord
{
    protected static string $resource = ArDestinationResource::class;

    // Zones are added on the edit page.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}

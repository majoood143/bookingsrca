<?php

namespace App\Filament\Resources\ArDestinationResource\Pages;

use App\Filament\Resources\ArDestinationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArDestination extends EditRecord
{
    protected static string $resource = ArDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

<?php

namespace App\Filament\Resources\ArItemResource\Pages;

use App\Filament\Resources\ArItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArItem extends EditRecord
{
    protected static string $resource = ArItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

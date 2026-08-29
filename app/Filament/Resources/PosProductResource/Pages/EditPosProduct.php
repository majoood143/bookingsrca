<?php

namespace App\Filament\Resources\PosProductResource\Pages;

use App\Filament\Resources\PosProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosProduct extends EditRecord
{
    protected static string $resource = PosProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

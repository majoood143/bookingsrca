<?php

namespace App\Filament\Resources\PosTaxResource\Pages;

use App\Filament\Resources\PosTaxResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosTax extends EditRecord
{
    protected static string $resource = PosTaxResource::class;

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

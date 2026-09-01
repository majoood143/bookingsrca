<?php

namespace App\Filament\Resources\PosBrandResource\Pages;

use App\Filament\Resources\PosBrandResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosBrand extends EditRecord
{
    protected static string $resource = PosBrandResource::class;

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

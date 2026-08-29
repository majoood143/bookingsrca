<?php

namespace App\Filament\Resources\PosProductCategoryResource\Pages;

use App\Filament\Resources\PosProductCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosProductCategory extends EditRecord
{
    protected static string $resource = PosProductCategoryResource::class;

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

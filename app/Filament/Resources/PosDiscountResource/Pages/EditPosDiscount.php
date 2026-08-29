<?php

namespace App\Filament\Resources\PosDiscountResource\Pages;

use App\Filament\Resources\PosDiscountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosDiscount extends EditRecord
{
    protected static string $resource = PosDiscountResource::class;

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

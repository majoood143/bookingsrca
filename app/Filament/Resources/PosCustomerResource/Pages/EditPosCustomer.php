<?php

namespace App\Filament\Resources\PosCustomerResource\Pages;

use App\Filament\Resources\PosCustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosCustomer extends EditRecord
{
    protected static string $resource = PosCustomerResource::class;

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

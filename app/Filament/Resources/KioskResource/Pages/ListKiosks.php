<?php

namespace App\Filament\Resources\KioskResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\KioskResource;
use Filament\Resources\Pages\ListRecords;

class ListKiosks extends ListRecords
{
    protected static string $resource = KioskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('kiosk.actions.new'))
                ->icon('heroicon-o-plus'),
        ];
    }
}

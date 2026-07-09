<?php

namespace App\Filament\Resources\KioskCardResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\KioskCardResource;
use Filament\Resources\Pages\ListRecords;

class ListKioskCards extends ListRecords
{
    protected static string $resource = KioskCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('kiosk_card.actions.new'))
                ->icon('heroicon-o-plus'),
        ];
    }
}

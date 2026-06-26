<?php

namespace App\Filament\Resources\ExtraServiceResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ExtraServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExtraServices extends ListRecords
{
    protected static string $resource = ExtraServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('extra_service.actions.new'))
                ->icon('heroicon-o-plus'),
        ];
    }
}

<?php

namespace App\Filament\Resources\GamePlayerResource\Pages;

use App\Filament\Resources\GamePlayerResource;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListGamePlayers extends ListRecords
{
    protected static string $resource = GamePlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The filtered table as it stands (campaign, consent, blocked filters apply).
            ExportAction::make()
                ->label(__('games.player.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn () => 'game-players-' . now()->format('Y-m-d-His')),
                ]),
        ];
    }
}

<?php

namespace App\Filament\Resources\PosShiftResource\Pages;

use App\Filament\Resources\PosShiftResource;
use App\Models\PosShift;
use App\Services\PosShiftService;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPosShift extends ViewRecord
{
    protected static string $resource = PosShiftResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(3)
                ->schema([
                    TextEntry::make('terminal.name')->label(__('pos.shift.fields.terminal')),
                    TextEntry::make('cashier.name')->label(__('pos.shift.fields.cashier')),
                    TextEntry::make('status')
                        ->label(__('pos.shift.fields.status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('pos.shift.status.' . $state)),

                    TextEntry::make('opening_float')->label(__('pos.shift.fields.opening_float')),
                    TextEntry::make('expected_cash')->label(__('pos.shift.fields.expected_cash'))->placeholder('—'),
                    TextEntry::make('counted_cash')->label(__('pos.shift.fields.counted_cash'))->placeholder('—'),

                    TextEntry::make('variance')
                        ->label(__('pos.shift.fields.variance'))
                        ->placeholder('—')
                        ->color(fn (?PosShift $record) => $record && $record->variance !== null && !app(PosShiftService::class)->isVarianceWithinTolerance($record) ? 'danger' : 'success'),

                    TextEntry::make('opened_at')->label(__('pos.shift.fields.opened_at'))->dateTime(),
                    TextEntry::make('closed_at')->label(__('pos.shift.fields.closed_at'))->dateTime()->placeholder('—'),
                ]),
        ]);
    }
}

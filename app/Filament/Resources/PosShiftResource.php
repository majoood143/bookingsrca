<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosShiftResource\Pages\ListPosShifts;
use App\Filament\Resources\PosShiftResource\Pages\ViewPosShift;
use App\Filament\Resources\PosShiftResource\RelationManagers\TransactionsRelationManager;
use App\Models\PosShift;
use App\Services\PosShiftService;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PosShiftResource extends Resource
{
    protected static ?string $model = PosShift::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 43;

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) \App\Models\BookingSetting::get('module_pos_enabled', true);
    }

    public static function canCreate(): bool
    {
        // Shifts are only opened/closed from the cashier POS screen.
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('pos.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('pos.shift.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.shift.plural');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('terminal.name')
                    ->label(__('pos.shift.fields.terminal'))
                    ->sortable(),

                TextColumn::make('cashier.name')
                    ->label(__('pos.shift.fields.cashier'))
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('pos.shift.fields.status'))
                    ->badge()
                    ->color(fn (string $state) => $state === 'open' ? 'success' : 'gray'),

                TextColumn::make('opening_float')
                    ->label(__('pos.shift.fields.opening_float')),

                TextColumn::make('variance')
                    ->label(__('pos.shift.fields.variance'))
                    ->color(fn (PosShift $record) => $record->variance !== null && !app(PosShiftService::class)->isVarianceWithinTolerance($record) ? 'danger' : null),

                TextColumn::make('opened_at')
                    ->label(__('pos.shift.fields.opened_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('closed_at')
                    ->label(__('pos.shift.fields.closed_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['open' => __('pos.shift.status.open'), 'closed' => __('pos.shift.status.closed')]),
                SelectFilter::make('terminal_id')
                    ->relationship('terminal', 'name')
                    ->label(__('pos.shift.fields.terminal')),
                SelectFilter::make('user_id')
                    ->relationship('cashier', 'name')
                    ->label(__('pos.shift.fields.cashier')),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('opened_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosShifts::route('/'),
            'view' => ViewPosShift::route('/{record}'),
        ];
    }
}

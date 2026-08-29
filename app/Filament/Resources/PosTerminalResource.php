<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosTerminalResource\Pages\CreatePosTerminal;
use App\Filament\Resources\PosTerminalResource\Pages\EditPosTerminal;
use App\Filament\Resources\PosTerminalResource\Pages\ListPosTerminals;
use App\Models\PosTerminal;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosTerminalResource extends Resource
{
    protected static ?string $model = PosTerminal::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?int $navigationSort = 40;

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) \App\Models\BookingSetting::get('module_pos_enabled', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('pos.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('pos.terminal.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.terminal.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('pos.terminal.fields.name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('location')
                                ->label(__('pos.terminal.fields.location'))
                                ->maxLength(255),
                        ]),

                    Grid::make(3)
                        ->schema([
                            TextInput::make('printer_config.host')
                                ->label(__('pos.terminal.fields.printer_ip'))
                                ->maxLength(255),

                            TextInput::make('printer_config.port')
                                ->label(__('pos.terminal.fields.printer_port'))
                                ->numeric()
                                ->default(9100),

                            TextInput::make('printer_config.paper_width_dots')
                                ->label(__('pos.terminal.fields.paper_width'))
                                ->numeric()
                                ->default(576),
                        ]),

                    Toggle::make('is_active')
                        ->label(__('pos.terminal.fields.is_active'))
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('pos.terminal.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label(__('pos.terminal.fields.location')),

                TextColumn::make('openShift.cashier.name')
                    ->label(__('pos.shift.fields.cashier'))
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label(__('pos.terminal.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosTerminals::route('/'),
            'create' => CreatePosTerminal::route('/create'),
            'edit' => EditPosTerminal::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosTaxResource\Pages\CreatePosTax;
use App\Filament\Resources\PosTaxResource\Pages\EditPosTax;
use App\Filament\Resources\PosTaxResource\Pages\ListPosTaxes;
use App\Models\PosTax;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosTaxResource extends Resource
{
    protected static ?string $model = PosTax::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';
    protected static ?int $navigationSort = 43;

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
        return __('pos.tax.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.tax.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('pos.tax.fields.name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('rate')
                                ->label(__('pos.tax.fields.rate'))
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(100),

                            Toggle::make('is_active')
                                ->label(__('pos.tax.fields.is_active'))
                                ->default(true)
                                ->inline(false),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('pos.tax.fields.name'))
                    ->searchable(),

                TextColumn::make('rate')
                    ->label(__('pos.tax.fields.rate'))
                    ->suffix('%'),

                IconColumn::make('is_active')
                    ->label(__('pos.tax.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosTaxes::route('/'),
            'create' => CreatePosTax::route('/create'),
            'edit' => EditPosTax::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosProductCategoryResource\Pages\CreatePosProductCategory;
use App\Filament\Resources\PosProductCategoryResource\Pages\EditPosProductCategory;
use App\Filament\Resources\PosProductCategoryResource\Pages\ListPosProductCategories;
use App\Models\PosProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosProductCategoryResource extends Resource
{
    protected static ?string $model = PosProductCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 41;

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
        return __('pos.category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.category.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('pos.category.fields.name'))
                                ->required()
                                ->maxLength(255),

                            Select::make('parent_id')
                                ->label(__('pos.category.fields.parent'))
                                ->helperText(__('pos.category.fields.parent_helper'))
                                ->options(fn (?PosProductCategory $record) => PosProductCategory::query()
                                    ->whereNull('parent_id')
                                    ->when($record, fn ($query) => $query->whereKeyNot($record->id))
                                    ->orderBy('order')
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->native(false),

                            TextInput::make('order')
                                ->label(__('pos.category.fields.order'))
                                ->numeric()
                                ->default(0),

                            Toggle::make('is_active')
                                ->label(__('pos.category.fields.is_active'))
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
                    ->label(__('pos.category.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label(__('pos.category.fields.parent'))
                    ->placeholder(__('(top-level)')),

                TextColumn::make('products_count')
                    ->label(__('pos.product.plural'))
                    ->counts('products'),

                IconColumn::make('is_active')
                    ->label(__('pos.category.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosProductCategories::route('/'),
            'create' => CreatePosProductCategory::route('/create'),
            'edit' => EditPosProductCategory::route('/{record}/edit'),
        ];
    }
}

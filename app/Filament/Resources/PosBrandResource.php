<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosBrandResource\Pages\CreatePosBrand;
use App\Filament\Resources\PosBrandResource\Pages\EditPosBrand;
use App\Filament\Resources\PosBrandResource\Pages\ListPosBrands;
use App\Models\PosBrand;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PosBrandResource extends Resource
{
    protected static ?string $model = PosBrand::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 41;

    public static function canAccess(): bool
    {
        return parent::canAccess()
            && (bool) \App\Models\BookingSetting::get('module_pos_enabled', true)
            && (bool) \App\Models\BookingSetting::get('module_pos_shop_enabled', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('pos.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('pos.brand.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.brand.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('pos.brand.fields.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                            TextInput::make('name_ar')
                                ->label(__('pos.brand.fields.name_ar'))
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->label(__('pos.brand.fields.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('order')
                                ->label(__('pos.brand.fields.order'))
                                ->numeric()
                                ->default(0),
                        ]),

                    FileUpload::make('logo')
                        ->label(__('pos.brand.fields.logo'))
                        ->image()
                        ->disk('public')
                        ->directory('pos-brands')
                        ->visibility('public')
                        ->imageEditor(),

                    Toggle::make('is_active')
                        ->label(__('pos.brand.fields.is_active'))
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label(__('pos.brand.fields.logo'))
                    ->disk('public')
                    ->square(),

                TextColumn::make('name')
                    ->label(__('pos.brand.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label(__('pos.product.plural'))
                    ->counts('products'),

                IconColumn::make('is_active')
                    ->label(__('pos.brand.fields.is_active'))
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('pos.brand.fields.is_active')),
            ])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosBrands::route('/'),
            'create' => CreatePosBrand::route('/create'),
            'edit' => EditPosBrand::route('/{record}/edit'),
        ];
    }
}

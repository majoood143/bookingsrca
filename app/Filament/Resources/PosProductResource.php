<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosProductResource\Pages\CreatePosProduct;
use App\Filament\Resources\PosProductResource\Pages\EditPosProduct;
use App\Filament\Resources\PosProductResource\Pages\ListPosProducts;
use App\Filament\Resources\PosProductResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\PosProductResource\RelationManagers\StockMovementsRelationManager;
use App\Models\PosProduct;
use App\Models\PosProductCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PosProductResource extends Resource
{
    protected static ?string $model = PosProduct::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
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
        return __('pos.product.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.product.plural');
    }

    public static function unitOptions(): array
    {
        return [
            'pcs' => __('pos.product.units.pcs'),
            'kg' => __('pos.product.units.kg'),
            'g' => __('pos.product.units.g'),
            'l' => __('pos.product.units.l'),
            'ml' => __('pos.product.units.ml'),
            'box' => __('pos.product.units.box'),
            'pack' => __('pos.product.units.pack'),
            'jar' => __('pos.product.units.jar'),
            'bottle' => __('pos.product.units.bottle'),
            'dozen' => __('pos.product.units.dozen'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('pos.product.sections.details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('pos.product.fields.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set, $get) => $get('slug') ?: $set('slug', Str::slug($state))),

                            TextInput::make('name_ar')
                                ->label(__('pos.product.fields.name_ar'))
                                ->maxLength(255),

                            Select::make('category_id')
                                ->label(__('pos.product.fields.category'))
                                ->options(fn () => PosProductCategory::query()
                                    ->orderBy('order')
                                    ->get()
                                    ->mapWithKeys(fn (PosProductCategory $category) => [$category->id => $category->full_name]))
                                ->searchable()
                                ->native(false)
                                ->createOptionForm([
                                    TextInput::make('name')->required()->maxLength(255),
                                    Select::make('parent_id')
                                        ->label(__('pos.category.fields.parent'))
                                        ->relationship('parent', 'name', fn ($query) => $query->whereNull('parent_id'))
                                        ->native(false),
                                ])
                                ->createOptionUsing(fn (array $data) => PosProductCategory::create($data)->id),

                            Textarea::make('description')
                                ->label(__('pos.product.fields.description'))
                                ->rows(3)
                                ->columnSpanFull(),

                            TextInput::make('sku')
                                ->label(__('pos.product.fields.sku'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('barcode')
                                ->label(__('pos.product.fields.barcode'))
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ]),

                    FileUpload::make('image')
                        ->label(__('pos.product.fields.image'))
                        ->image()
                        ->disk('public')
                        ->directory('pos-products')
                        ->visibility('public')
                        ->imageEditor(),
                ]),

            Section::make(__('pos.product.sections.online_store'))
                ->description(__('pos.product.sections.online_store_desc'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('brand_id')
                                ->label(__('pos.product.fields.brand'))
                                ->relationship('brand', 'name')
                                ->searchable()
                                ->native(false),

                            TextInput::make('slug')
                                ->label(__('pos.product.fields.slug'))
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->helperText(__('pos.product.fields.slug_helper')),
                        ]),

                    Textarea::make('description_ar')
                        ->label(__('pos.product.fields.description_ar'))
                        ->rows(3)
                        ->columnSpanFull(),

                    FileUpload::make('gallery_images')
                        ->label(__('pos.product.fields.gallery_images'))
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->disk('public')
                        ->directory('pos-products/gallery')
                        ->visibility('public'),

                    Toggle::make('is_published_online')
                        ->label(__('pos.product.fields.is_published_online'))
                        ->helperText(__('pos.product.fields.is_published_online_helper'))
                        ->inline(false),
                ])
                ->collapsible(),

            Section::make(__('pos.product.sections.pricing_stock'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('price')
                                ->label(__('pos.product.fields.price'))
                                ->required()
                                ->numeric()
                                ->step(0.001)
                                ->minValue(0),

                            TextInput::make('tax_rate')
                                ->label(__('pos.product.fields.tax_rate'))
                                ->required()
                                ->numeric()
                                ->step(0.01)
                                ->default(5)
                                ->minValue(0)
                                ->maxValue(100),

                            Select::make('unit')
                                ->label(__('pos.product.fields.unit'))
                                ->options(self::unitOptions())
                                ->default('pcs')
                                ->required()
                                ->native(false),

                            TextInput::make('stock_quantity')
                                ->label(__('pos.product.fields.stock_quantity'))
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            TextInput::make('low_stock_threshold')
                                ->label(__('pos.product.fields.low_stock_threshold'))
                                ->numeric()
                                ->minValue(0),
                        ]),
                ]),

            Section::make(__('pos.product.sections.location'))
                ->description(__('pos.product.sections.location_desc'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('rack')
                                ->label(__('pos.product.fields.rack'))
                                ->maxLength(255),

                            TextInput::make('row')
                                ->label(__('pos.product.fields.row'))
                                ->maxLength(255),

                            TextInput::make('position')
                                ->label(__('pos.product.fields.position'))
                                ->maxLength(255),
                        ]),
                ])
                ->collapsible(),

            Toggle::make('is_active')
                ->label(__('pos.product.fields.is_active'))
                ->default(true)
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('pos.product.fields.image'))
                    ->disk('public')
                    ->square(),

                TextColumn::make('name')
                    ->label(__('pos.product.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.full_name')
                    ->label(__('pos.product.fields.category'))
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('sku')
                    ->label(__('pos.product.fields.sku'))
                    ->searchable(),

                TextColumn::make('price')
                    ->label(__('pos.product.fields.price'))
                    ->html()
                    ->formatStateUsing(fn ($state) => view('partials.currency-amount', ['amount' => (float) $state])->render()),

                TextColumn::make('stock_quantity')
                    ->label(__('pos.product.fields.stock_quantity'))
                    ->badge()
                    ->color(fn (PosProduct $record) => $record->isLowStock() ? 'danger' : 'success')
                    ->formatStateUsing(fn (PosProduct $record) => $record->stock_quantity . ' ' . (self::unitOptions()[$record->unit] ?? $record->unit))
                    ->sortable(),

                TextColumn::make('location_label')
                    ->label(__('pos.product.fields.location'))
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('pos.product.fields.is_active'))
                    ->boolean(),

                IconColumn::make('is_published_online')
                    ->label(__('pos.product.fields.is_published_online'))
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('pos.product.fields.category'))
                    ->relationship('category', 'name'),
                SelectFilter::make('brand_id')
                    ->label(__('pos.product.fields.brand'))
                    ->relationship('brand', 'name'),
                TernaryFilter::make('is_active')
                    ->label(__('pos.product.fields.is_active')),
                TernaryFilter::make('is_published_online')
                    ->label(__('pos.product.fields.is_published_online')),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            StockMovementsRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosProducts::route('/'),
            'create' => CreatePosProduct::route('/create'),
            'edit' => EditPosProduct::route('/{record}/edit'),
        ];
    }
}

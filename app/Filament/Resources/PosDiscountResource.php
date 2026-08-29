<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosDiscountResource\Pages\CreatePosDiscount;
use App\Filament\Resources\PosDiscountResource\Pages\EditPosDiscount;
use App\Filament\Resources\PosDiscountResource\Pages\ListPosDiscounts;
use App\Models\PosDiscount;
use Filament\Forms\Components\DateTimePicker;
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

class PosDiscountResource extends Resource
{
    protected static ?string $model = PosDiscount::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 42;

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
        return __('pos.discount.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.discount.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('code')
                                ->label(__('pos.discount.fields.code'))
                                ->helperText(__('pos.discount.fields.code_helper'))
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            Select::make('type')
                                ->label(__('pos.discount.fields.type'))
                                ->options(['percentage' => __('Percentage'), 'fixed_amount' => __('Fixed Amount')])
                                ->required()
                                ->native(false),

                            TextInput::make('value')
                                ->label(__('pos.discount.fields.value'))
                                ->numeric()
                                ->required()
                                ->minValue(0),

                            Select::make('applies_to')
                                ->label(__('pos.discount.fields.applies_to'))
                                ->options(['tickets' => __('Tickets'), 'merchandise' => __('Merchandise'), 'all' => __('All')])
                                ->default('all')
                                ->required()
                                ->native(false),

                            TextInput::make('max_uses')
                                ->label(__('pos.discount.fields.max_uses'))
                                ->numeric()
                                ->minValue(1),

                            DateTimePicker::make('expires_at')
                                ->label(__('pos.discount.fields.expires_at')),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Toggle::make('requires_approval')
                                ->label(__('pos.discount.fields.requires_approval'))
                                ->inline(false),

                            Toggle::make('is_active')
                                ->label(__('pos.discount.fields.is_active'))
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
                TextColumn::make('code')
                    ->label(__('pos.discount.fields.code'))
                    ->placeholder(__('(manager discretion)'))
                    ->searchable(),

                TextColumn::make('type')
                    ->label(__('pos.discount.fields.type')),

                TextColumn::make('value')
                    ->label(__('pos.discount.fields.value')),

                TextColumn::make('applies_to')
                    ->label(__('pos.discount.fields.applies_to'))
                    ->badge(),

                TextColumn::make('used_count')
                    ->label(__('pos.discount.fields.used_count')),

                IconColumn::make('requires_approval')
                    ->label(__('pos.discount.fields.requires_approval'))
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label(__('pos.discount.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosDiscounts::route('/'),
            'create' => CreatePosDiscount::route('/create'),
            'edit' => EditPosDiscount::route('/{record}/edit'),
        ];
    }
}

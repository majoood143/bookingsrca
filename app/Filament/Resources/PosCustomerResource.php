<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosCustomerResource\Pages\CreatePosCustomer;
use App\Filament\Resources\PosCustomerResource\Pages\EditPosCustomer;
use App\Filament\Resources\PosCustomerResource\Pages\ListPosCustomers;
use App\Models\PosCustomer;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosCustomerResource extends Resource
{
    protected static ?string $model = PosCustomer::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
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
        return __('pos.customer.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.customer.plural');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('is_walk_in', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Radio::make('type')
                        ->label(__('pos.customer.label'))
                        ->options([
                            'individual' => __('pos.customer.type_individual'),
                            'business' => __('pos.customer.type_business'),
                        ])
                        ->default('individual')
                        ->inline()
                        ->required(),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('pos.customer.fields.name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('business_name')
                                ->label(__('pos.customer.fields.business_name'))
                                ->maxLength(255)
                                ->visible(fn ($get) => $get('type') === 'business'),

                            TextInput::make('mobile')
                                ->label(__('pos.customer.fields.mobile'))
                                ->required()
                                ->tel()
                                ->maxLength(50),

                            TextInput::make('alternate_contact')
                                ->label(__('pos.customer.fields.alternate_contact'))
                                ->tel()
                                ->maxLength(50),

                            TextInput::make('landline')
                                ->label(__('pos.customer.fields.landline'))
                                ->tel()
                                ->maxLength(50),

                            TextInput::make('email')
                                ->label(__('pos.customer.fields.email'))
                                ->email()
                                ->maxLength(255),
                        ]),
                ]),

            Section::make(__('pos.customer.more_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('address_line_1')
                                ->label(__('pos.customer.fields.address_line_1'))
                                ->maxLength(255)
                                ->columnSpanFull(),

                            TextInput::make('address_line_2')
                                ->label(__('pos.customer.fields.address_line_2'))
                                ->maxLength(255)
                                ->columnSpanFull(),

                            TextInput::make('city')
                                ->label(__('pos.customer.fields.city'))
                                ->maxLength(255),

                            TextInput::make('state')
                                ->label(__('pos.customer.fields.state'))
                                ->maxLength(255),

                            TextInput::make('country')
                                ->label(__('pos.customer.fields.country'))
                                ->maxLength(255),

                            TextInput::make('zip_code')
                                ->label(__('pos.customer.fields.zip_code'))
                                ->maxLength(50),
                        ]),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label(__('pos.customer.fields.name'))
                    ->searchable(['name', 'business_name'])
                    ->sortable(),

                TextColumn::make('mobile')
                    ->label(__('pos.customer.fields.mobile'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('pos.customer.fields.email'))
                    ->placeholder('—'),

                TextColumn::make('transactions_count')
                    ->label(__('pos.transaction.plural'))
                    ->counts('transactions'),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosCustomers::route('/'),
            'create' => CreatePosCustomer::route('/create'),
            'edit' => EditPosCustomer::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use App\Filament\Resources\KioskCardResource\Pages\ListKioskCards;
use App\Filament\Resources\KioskCardResource\Pages\CreateKioskCard;
use App\Filament\Resources\KioskCardResource\Pages\EditKioskCard;
use App\Filament\Resources\KioskCardResource\RelationManagers\TransactionsRelationManager;
use App\Models\KioskCard;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class KioskCardResource extends Resource
{
    protected static ?string $model = KioskCard::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): ?string
    {
        return __('kiosk_card.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('kiosk_card.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('kiosk_card.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('kiosk_card.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('kiosk_card.sections.information'))
                    ->description(__('kiosk_card.sections.information_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('uid')
                                    ->label(__('kiosk_card.fields.uid'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->disabled(fn($record) => $record !== null)
                                    ->dehydrated()
                                    ->helperText(__('kiosk_card.fields.uid_helper')),

                                Select::make('status')
                                    ->label(__('kiosk_card.fields.status'))
                                    ->options(__('kiosk_card.statuses'))
                                    ->default('active')
                                    ->required()
                                    ->native(false),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('holder_name')
                                    ->label(__('kiosk_card.fields.holder_name'))
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label(__('kiosk_card.fields.phone'))
                                    ->tel()
                                    ->maxLength(255),
                            ]),

                        Textarea::make('notes')
                            ->label(__('kiosk_card.fields.notes'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make(__('kiosk_card.sections.balance'))
                    ->description(__('kiosk_card.sections.balance_desc'))
                    ->schema([
                        TextInput::make('balance')
                            ->label(__('kiosk_card.fields.balance'))
                            ->numeric()
                            ->prefix('OMR')
                            ->minValue(0)
                            ->default(0)
                            ->disabled(fn($record) => $record !== null)
                            ->dehydrated(fn($record) => $record === null)
                            ->helperText(fn($record) => $record !== null
                                ? __('kiosk_card.actions.top_up') . ' →'
                                : null),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uid')
                    ->label(__('kiosk_card.columns.uid'))
                    ->badge()
                    ->searchable(),

                TextColumn::make('holder_name')
                    ->label(__('kiosk_card.columns.holder_name'))
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('phone')
                    ->label(__('kiosk_card.columns.phone'))
                    ->placeholder('-'),

                TextColumn::make('balance')
                    ->label(__('kiosk_card.columns.balance'))
                    ->money('OMR')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('kiosk_card.columns.status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => __('kiosk_card.statuses.' . $state))
                    ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),

                TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label(__('kiosk_card.columns.transactions_count'))
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label(__('kiosk_card.columns.created_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('kiosk_card.fields.status'))
                    ->options(__('kiosk_card.statuses')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('top_up')
                        ->label(__('kiosk_card.actions.top_up'))
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->schema([
                            TextInput::make('amount')
                                ->label(__('kiosk_card.top_up_modal.amount'))
                                ->numeric()
                                ->minValue(0.01)
                                ->prefix('OMR')
                                ->required(),

                            TextInput::make('reference')
                                ->label(__('kiosk_card.top_up_modal.reference'))
                                ->maxLength(255),

                            Textarea::make('notes')
                                ->label(__('kiosk_card.top_up_modal.notes'))
                                ->rows(2),
                        ])
                        ->modalHeading(__('kiosk_card.top_up_modal.heading'))
                        ->modalDescription(__('kiosk_card.top_up_modal.description'))
                        ->action(function (KioskCard $record, array $data) {
                            DB::transaction(function () use ($record, $data) {
                                $card = KioskCard::whereKey($record->id)->lockForUpdate()->first();

                                $card->applyTransaction('topup', (float) $data['amount'], [
                                    'recorded_by' => auth()->id(),
                                    'reference'   => $data['reference'] ?? null,
                                ]);
                            });

                            Notification::make()
                                ->success()
                                ->title(__('kiosk_card.notifications.topped_up'))
                                ->send();
                        })
                        ->disabled(fn($record) => $record->status !== 'active'),

                    Action::make('toggle_status')
                        ->label(fn($record) => $record->status === 'active' ? __('kiosk_card.actions.block') : __('kiosk_card.actions.unblock'))
                        ->icon(fn($record) => $record->status === 'active' ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                        ->color(fn($record) => $record->status === 'active' ? 'danger' : 'success')
                        ->action(function (KioskCard $record) {
                            $record->update(['status' => $record->status === 'active' ? 'blocked' : 'active']);

                            Notification::make()
                                ->success()
                                ->title($record->status === 'active' ? __('kiosk_card.notifications.card_unblocked') : __('kiosk_card.notifications.card_blocked'))
                                ->send();
                        })
                        ->requiresConfirmation(),

                    DeleteAction::make(),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->emptyStateActions([
                CreateAction::make()->label(__('kiosk_card.actions.new')),
            ])
            ->emptyStateHeading(__('kiosk_card.empty_state.heading'))
            ->emptyStateDescription(__('kiosk_card.empty_state.description'))
            ->emptyStateIcon('heroicon-o-credit-card');
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
            'index'  => ListKioskCards::route('/'),
            'create' => CreateKioskCard::route('/create'),
            'edit'   => EditKioskCard::route('/{record}/edit'),
        ];
    }
}

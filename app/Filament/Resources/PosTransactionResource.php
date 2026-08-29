<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosTransactionResource\Pages\ListPosTransactions;
use App\Filament\Resources\PosTransactionResource\Pages\ViewPosTransaction;
use App\Filament\Resources\PosTransactionResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\PosTransactionResource\RelationManagers\PaymentsRelationManager;
use App\Models\PosTransaction;
use App\Services\Printing\ThermalPrintService;
use App\Services\PosTransactionService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PosTransactionResource extends Resource
{
    protected static ?string $model = PosTransaction::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?int $navigationSort = 44;

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) \App\Models\BookingSetting::get('module_pos_enabled', true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('pos.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('pos.transaction.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.transaction.plural');
    }

    protected static function canManagerOverride(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['super_admin', 'pos_manager']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->label(__('pos.transaction.fields.transaction_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('terminal.name')
                    ->label(__('pos.transaction.fields.terminal'))
                    ->sortable(),

                TextColumn::make('cashier.name')
                    ->label(__('pos.transaction.fields.cashier'))
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('pos.transaction.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('pos.transaction.status.' . $state))
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'voided' => 'gray',
                        'refunded', 'partially_refunded' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('grand_total')
                    ->label(__('pos.transaction.fields.grand_total'))
                    ->html()
                    ->formatStateUsing(fn ($state) => view('partials.currency-amount', ['amount' => (float) $state])->render())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('pos.transaction.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'completed' => __('pos.transaction.status.completed'),
                        'refunded' => __('pos.transaction.status.refunded'),
                        'partially_refunded' => __('pos.transaction.status.partially_refunded'),
                        'voided' => __('pos.transaction.status.voided'),
                    ]),
                SelectFilter::make('terminal_id')
                    ->relationship('terminal', 'name')
                    ->label(__('pos.transaction.fields.terminal')),
                SelectFilter::make('cashier_id')
                    ->relationship('cashier', 'name')
                    ->label(__('pos.transaction.fields.cashier')),
                Filter::make('created_at')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from'),
                        \Filament\Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('reprint')
                    ->label(__('pos.transaction.actions.reprint'))
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->action(function (PosTransaction $record) {
                        try {
                            app(ThermalPrintService::class)->enqueuePosReceipt($record);
                            Notification::make()->success()->title(__('pos.transaction.actions.reprint'))->send();
                        } catch (Exception $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),

                Action::make('void')
                    ->label(__('pos.transaction.actions.void'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (PosTransaction $record) => $record->status === 'completed'
                        && ($record->shift->isOpen() || static::canManagerOverride()))
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')->required(),
                    ])
                    ->action(function (PosTransaction $record, array $data) {
                        try {
                            app(PosTransactionService::class)->voidTransaction($record, $data['reason']);
                            Notification::make()->success()->title(__('pos.transaction.notifications.voided'))->send();
                        } catch (Exception $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),

                Action::make('refund')
                    ->label(__('pos.transaction.actions.refund'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (PosTransaction $record) => $record->status === 'completed' && static::canManagerOverride())
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label(__('pos.transaction.actions.refund_reason'))
                            ->required(),
                    ])
                    ->action(function (PosTransaction $record, array $data) {
                        try {
                            app(PosTransactionService::class)->refundTransaction($record, $data['reason']);
                            Notification::make()->success()->title(__('pos.transaction.notifications.refunded'))->send();
                        } catch (Exception $e) {
                            Notification::make()->danger()->title($e->getMessage())->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosTransactions::route('/'),
            'view' => ViewPosTransaction::route('/{record}'),
        ];
    }
}

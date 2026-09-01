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
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

                TextColumn::make('customer.display_name')
                    ->label(__('pos.customer.label'))
                    ->default(__('pos.customer.walk_in'))
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

                ActionGroup::make([
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
                    ->label(__('pos.transaction.actions.group_status'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray'),

                ActionGroup::make([
                    Action::make('reprint')
                        ->label(__('pos.transaction.actions.reprint'))
                        ->icon('heroicon-o-printer')
                        ->action(function (PosTransaction $record) {
                            try {
                                app(ThermalPrintService::class)->enqueuePosReceipt($record);
                                Notification::make()->success()->title(__('pos.transaction.actions.reprint'))->send();
                            } catch (Exception $e) {
                                Notification::make()->danger()->title($e->getMessage())->send();
                            }
                        }),

                    Action::make('editShipping')
                        ->label(__('pos.transaction.actions.edit_shipping'))
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Textarea::make('shipping_details')->label(__('pos.screen.shipping_modal.details')),
                            Textarea::make('shipping_address')->label(__('pos.screen.shipping_modal.address')),
                            TextInput::make('shipping_charges')->label(__('pos.screen.shipping_modal.charges'))->numeric(),
                            Select::make('shipping_status')
                                ->label(__('pos.screen.shipping_modal.status'))
                                ->options([
                                    'pending' => __('pos.screen.shipping_modal.statuses.pending'),
                                    'shipped' => __('pos.screen.shipping_modal.statuses.shipped'),
                                    'delivered' => __('pos.screen.shipping_modal.statuses.delivered'),
                                    'cancelled' => __('pos.screen.shipping_modal.statuses.cancelled'),
                                ])
                                ->native(false),
                            TextInput::make('delivered_to')->label(__('pos.screen.shipping_modal.delivered_to')),
                            Select::make('delivery_person_id')
                                ->label(__('pos.screen.shipping_modal.delivery_person'))
                                ->relationship('deliveryPerson', 'name')
                                ->searchable()
                                ->preload(),
                        ])
                        ->fillForm(fn (PosTransaction $record) => $record->only([
                            'shipping_details', 'shipping_address', 'shipping_charges', 'shipping_status', 'delivered_to', 'delivery_person_id',
                        ]))
                        ->action(function (PosTransaction $record, array $data) {
                            $record->update($data);

                            Notification::make()->success()->title(__('pos.transaction.notifications.shipping_updated'))->send();
                        }),

                    Action::make('viewPayments')
                        ->label(__('pos.transaction.actions.view_payments'))
                        ->icon('heroicon-o-banknotes')
                        ->modalHeading(fn (PosTransaction $record) => __('pos.transaction.actions.view_payments') . ' - ' . $record->transaction_number)
                        ->modalContent(fn (PosTransaction $record) => view('filament.modals.pos-payments', [
                            'payments' => $record->payments,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('pos.screen.cancel'))
                        ->modalWidth('2xl'),

                    Action::make('invoiceUrl')
                        ->label(__('pos.transaction.actions.invoice_url'))
                        ->icon('heroicon-o-document-text')
                        ->action(fn (PosTransaction $record) => static::downloadInvoice($record)),

                    Action::make('packagingSlip')
                        ->label(__('pos.transaction.actions.packaging_slip'))
                        ->icon('heroicon-o-archive-box')
                        ->action(fn (PosTransaction $record) => static::downloadPackagingSlip($record)),

                    Action::make('deliveryNote')
                        ->label(__('pos.transaction.actions.delivery_note'))
                        ->icon('heroicon-o-truck')
                        ->action(fn (PosTransaction $record) => static::downloadDeliveryNote($record)),

                    DeleteAction::make()
                        ->label(__('pos.transaction.actions.delete'))
                        ->before(function (PosTransaction $record) {
                            if (!$record->canBeDeleted()) {
                                Notification::make()->danger()->title(__('pos.transaction.notifications.cannot_delete'))->send();

                                return false;
                            }
                        })
                        ->successNotificationTitle(__('pos.transaction.notifications.deleted')),
                ])
                    ->label(__('pos.transaction.actions.group_more'))
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function downloadInvoice(PosTransaction $record): StreamedResponse
    {
        $locale = app()->getLocale();
        $record->loadMissing(['items.itemable', 'terminal', 'cashier', 'customer']);

        $filename = __('pos.transaction.invoice.filename', ['number' => $record->transaction_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-transaction-invoice-pdf', [
            'transaction' => $record,
            'locale' => $locale,
        ])->format('a4');

        return Response::streamDownload(
            fn () => print($pdf->generatePdfContent()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected static function downloadPackagingSlip(PosTransaction $record): StreamedResponse
    {
        $locale = app()->getLocale();
        $record->loadMissing(['items.itemable', 'customer']);

        $filename = __('pos.packaging_slip.filename', ['number' => $record->transaction_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-packaging-slip-pdf', [
            'documentTitle' => __('pos.packaging_slip.document_title', [], $locale),
            'number' => $record->transaction_number,
            'date' => $record->created_at,
            'customerName' => $record->customer?->display_name ?? __('pos.customer.walk_in', [], $locale),
            'customerPhone' => $record->customer?->mobile,
            'address' => $record->shipping_address,
            'items' => static::itemLines($record, $locale),
            'locale' => $locale,
        ])->format('a4');

        return Response::streamDownload(
            fn () => print($pdf->generatePdfContent()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected static function downloadDeliveryNote(PosTransaction $record): StreamedResponse
    {
        $locale = app()->getLocale();
        $record->loadMissing(['items.itemable', 'customer', 'deliveryPerson']);

        $filename = __('pos.delivery_note.filename', ['number' => $record->transaction_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-delivery-note-pdf', [
            'documentTitle' => __('pos.delivery_note.document_title', [], $locale),
            'number' => $record->transaction_number,
            'date' => $record->created_at,
            'customerName' => $record->delivered_to ?: ($record->customer?->display_name ?? __('pos.customer.walk_in', [], $locale)),
            'customerPhone' => $record->customer?->mobile,
            'address' => $record->shipping_address,
            'deliveryPerson' => $record->deliveryPerson?->name,
            'status' => $record->shipping_status ? __('pos.screen.shipping_modal.statuses.' . $record->shipping_status, [], $locale) : null,
            'items' => static::itemLines($record, $locale),
            'locale' => $locale,
        ])->format('a4');

        return Response::streamDownload(
            fn () => print($pdf->generatePdfContent()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected static function itemLines(PosTransaction $record, string $locale)
    {
        return $record->items->map(fn ($item) => [
            'name' => $item->isTicket()
                ? ($item->itemable?->ticketType?->getTranslation('name', $locale) ?? '')
                : ($item->itemable?->name ?? ''),
            'qty' => $item->quantity,
        ]);
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

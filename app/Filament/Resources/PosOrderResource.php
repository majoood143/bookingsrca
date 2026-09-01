<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosOrderResource\Pages\ListPosOrders;
use App\Filament\Resources\PosOrderResource\Pages\ViewPosOrder;
use App\Filament\Resources\PosOrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\PosOrderResource\RelationManagers\PaymentsRelationManager;
use App\Models\PosOrder;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PosOrderResource extends Resource
{
    protected static ?string $model = PosOrder::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 46;

    public static function canAccess(): bool
    {
        return parent::canAccess()
            && (bool) \App\Models\BookingSetting::get('module_pos_enabled', true)
            && (bool) \App\Models\BookingSetting::get('module_pos_shop_enabled', true);
    }

    // Online orders are only ever created by the storefront checkout flow.
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
        return __('pos.order.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('pos.order.plural');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('pos.order.fields.order_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.display_name')
                    ->label(__('pos.order.fields.customer'))
                    ->description(fn (PosOrder $record) => $record->contact_email)
                    ->searchable(['contact_name', 'contact_email']),

                TextColumn::make('status')
                    ->label(__('pos.order.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('pos.order.status.' . $state))
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'failed', 'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('fulfillment_status')
                    ->label(__('pos.order.fields.fulfillment_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('pos.order.fulfillment_status.' . $state))
                    ->color(fn (string $state) => match ($state) {
                        'ready' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'preparing' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('grand_total')
                    ->label(__('pos.order.fields.grand_total'))
                    ->html()
                    ->formatStateUsing(fn ($state) => view('partials.currency-amount', ['amount' => (float) $state])->render())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('pos.order.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('pos.order.fields.status'))
                    ->options([
                        'pending_payment' => __('pos.order.status.pending_payment'),
                        'paid' => __('pos.order.status.paid'),
                        'failed' => __('pos.order.status.failed'),
                        'cancelled' => __('pos.order.status.cancelled'),
                    ]),
                SelectFilter::make('fulfillment_status')
                    ->label(__('pos.order.fields.fulfillment_status'))
                    ->options([
                        'pending' => __('pos.order.fulfillment_status.pending'),
                        'preparing' => __('pos.order.fulfillment_status.preparing'),
                        'ready' => __('pos.order.fulfillment_status.ready'),
                        'completed' => __('pos.order.fulfillment_status.completed'),
                        'cancelled' => __('pos.order.fulfillment_status.cancelled'),
                    ]),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
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
                    Action::make('markPreparing')
                        ->label(__('pos.order.actions.mark_preparing'))
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn (PosOrder $record) => $record->isPaid() && $record->fulfillment_status === 'pending')
                        ->action(fn (PosOrder $record) => static::transition($record, 'preparing')),

                    Action::make('markReady')
                        ->label(__('pos.order.actions.mark_ready'))
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->visible(fn (PosOrder $record) => $record->isPaid() && $record->fulfillment_status === 'preparing')
                        ->action(fn (PosOrder $record) => static::transition($record, 'ready')),

                    Action::make('markCompleted')
                        ->label(__('pos.order.actions.mark_completed'))
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (PosOrder $record) => $record->isPaid() && $record->fulfillment_status === 'ready')
                        ->action(fn (PosOrder $record) => static::transition($record, 'completed')),

                    Action::make('markCancelled')
                        ->label(__('pos.order.actions.mark_cancelled'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (PosOrder $record) => $record->isPaid()
                            && !in_array($record->fulfillment_status, ['completed', 'cancelled'], true))
                        ->action(fn (PosOrder $record) => static::transition($record, 'cancelled')),
                ])
                    ->label(__('pos.order.actions.group_fulfillment'))
                    ->icon('heroicon-o-truck')
                    ->color('gray'),

                ActionGroup::make([
                    Action::make('editShipping')
                        ->label(__('pos.order.actions.edit_shipping'))
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            TextInput::make('address_line_1')->label(__('pos.customer.fields.address_line_1')),
                            TextInput::make('address_line_2')->label(__('pos.customer.fields.address_line_2')),
                            TextInput::make('city')->label(__('pos.customer.fields.city')),
                            TextInput::make('state')->label(__('pos.customer.fields.state')),
                            TextInput::make('country')->label(__('pos.customer.fields.country')),
                            TextInput::make('zip_code')->label(__('pos.customer.fields.zip_code')),
                        ])
                        ->fillForm(fn (PosOrder $record) => $record->only([
                            'address_line_1', 'address_line_2', 'city', 'state', 'country', 'zip_code',
                        ]))
                        ->action(function (PosOrder $record, array $data) {
                            $record->update($data);

                            Notification::make()->success()->title(__('pos.order.notifications.shipping_updated'))->send();
                        }),

                    Action::make('viewPayments')
                        ->label(__('pos.order.actions.view_payments'))
                        ->icon('heroicon-o-banknotes')
                        ->modalHeading(fn (PosOrder $record) => __('pos.order.actions.view_payments') . ' - ' . $record->order_number)
                        ->modalContent(fn (PosOrder $record) => view('filament.modals.pos-payments', [
                            'payments' => $record->payments,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('pos.screen.cancel'))
                        ->modalWidth('2xl'),

                    Action::make('printReceipt')
                        ->label(__('pos.order.actions.print_receipt'))
                        ->icon('heroicon-o-printer')
                        ->action(fn (PosOrder $record) => static::downloadReceipt($record)),

                    Action::make('invoiceUrl')
                        ->label(__('pos.order.actions.invoice_url'))
                        ->icon('heroicon-o-document-text')
                        ->action(fn (PosOrder $record) => static::downloadReceipt($record, isInvoice: true)),

                    Action::make('packagingSlip')
                        ->label(__('pos.order.actions.packaging_slip'))
                        ->icon('heroicon-o-archive-box')
                        ->action(fn (PosOrder $record) => static::downloadPackagingSlip($record)),

                    Action::make('deliveryNote')
                        ->label(__('pos.order.actions.delivery_note'))
                        ->icon('heroicon-o-truck')
                        ->action(fn (PosOrder $record) => static::downloadDeliveryNote($record)),

                    DeleteAction::make()
                        ->label(__('pos.order.actions.delete'))
                        ->before(function (PosOrder $record) {
                            if (!$record->canBeDeleted()) {
                                Notification::make()->danger()->title(__('pos.order.notifications.cannot_delete'))->send();

                                return false;
                            }
                        })
                        ->successNotificationTitle(__('pos.order.notifications.deleted')),
                ])
                    ->label(__('pos.order.actions.group_more'))
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function transition(PosOrder $record, string $fulfillmentStatus): void
    {
        $record->update(['fulfillment_status' => $fulfillmentStatus]);

        Notification::make()->success()->title(__('pos.order.notifications.status_updated'))->send();
    }

    protected static function downloadReceipt(PosOrder $record, bool $isInvoice = false): StreamedResponse
    {
        $locale = app()->getLocale();
        $record->loadMissing('items');

        $key = $isInvoice ? 'invoice' : 'receipt';
        $filename = __('pos.order.' . $key . '.filename', ['number' => $record->order_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-order-receipt-pdf', [
            'order' => $record,
            'locale' => $locale,
            'documentTitle' => $isInvoice ? __('pos.order.invoice.document_title', [], $locale) : null,
        ])->format('a4');

        return Response::streamDownload(
            fn () => print($pdf->generatePdfContent()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected static function downloadPackagingSlip(PosOrder $record): StreamedResponse
    {
        $locale = app()->getLocale();
        $record->loadMissing('items');

        $filename = __('pos.packaging_slip.filename', ['number' => $record->order_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-packaging-slip-pdf', [
            'documentTitle' => __('pos.packaging_slip.document_title', [], $locale),
            'number' => $record->order_number,
            'date' => $record->created_at,
            'customerName' => $record->contact_name,
            'customerPhone' => $record->contact_mobile,
            'address' => static::addressLabel($record),
            'items' => $record->items->map(fn ($item) => ['name' => $item->product_name, 'qty' => $item->quantity]),
            'locale' => $locale,
        ])->format('a4');

        return Response::streamDownload(
            fn () => print($pdf->generatePdfContent()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected static function downloadDeliveryNote(PosOrder $record): StreamedResponse
    {
        $locale = app()->getLocale();
        $record->loadMissing('items');

        $filename = __('pos.delivery_note.filename', ['number' => $record->order_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-delivery-note-pdf', [
            'documentTitle' => __('pos.delivery_note.document_title', [], $locale),
            'number' => $record->order_number,
            'date' => $record->created_at,
            'customerName' => $record->contact_name,
            'customerPhone' => $record->contact_mobile,
            'address' => static::addressLabel($record),
            'deliveryPerson' => null,
            'status' => __('pos.order.fulfillment_status.' . $record->fulfillment_status, [], $locale),
            'items' => $record->items->map(fn ($item) => ['name' => $item->product_name, 'qty' => $item->quantity]),
            'locale' => $locale,
        ])->format('a4');

        return Response::streamDownload(
            fn () => print($pdf->generatePdfContent()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    protected static function addressLabel(PosOrder $record): string
    {
        return implode(', ', array_filter([
            $record->address_line_1, $record->address_line_2, $record->city, $record->state, $record->country, $record->zip_code,
        ]));
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
            'index' => ListPosOrders::route('/'),
            'view' => ViewPosOrder::route('/{record}'),
        ];
    }
}

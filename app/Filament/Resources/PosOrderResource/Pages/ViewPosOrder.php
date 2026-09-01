<?php

namespace App\Filament\Resources\PosOrderResource\Pages;

use App\Filament\Resources\PosOrderResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPosOrder extends ViewRecord
{
    protected static string $resource = PosOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(3)
                ->schema([
                    TextEntry::make('order_number')->label(__('pos.order.fields.order_number')),
                    TextEntry::make('status')
                        ->label(__('pos.order.fields.status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('pos.order.status.' . $state)),
                    TextEntry::make('fulfillment_status')
                        ->label(__('pos.order.fields.fulfillment_status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('pos.order.fulfillment_status.' . $state)),

                    TextEntry::make('contact_name')->label(__('pos.order.fields.contact_name')),
                    TextEntry::make('contact_email')->label(__('pos.order.fields.contact_email')),
                    TextEntry::make('contact_mobile')->label(__('pos.order.fields.contact_mobile')),

                    TextEntry::make('subtotal')->label(__('pos.order.fields.subtotal')),
                    TextEntry::make('discount_total')->label(__('pos.order.fields.discount_total')),
                    TextEntry::make('tax_total')->label(__('pos.order.fields.tax_total')),
                    TextEntry::make('grand_total')->label(__('pos.order.fields.grand_total'))->weight('bold'),

                    TextEntry::make('payment_method')->label(__('pos.order.fields.payment_method'))->placeholder('—'),
                    TextEntry::make('payment_reference')->label(__('pos.order.fields.payment_reference'))->placeholder('—'),
                    TextEntry::make('paid_at')->label(__('pos.order.fields.paid_at'))->dateTime()->placeholder('—'),

                    TextEntry::make('addressLabel')
                        ->label(__('pos.order.fields.address'))
                        ->state(fn ($record) => implode(', ', array_filter([
                            $record->address_line_1, $record->address_line_2, $record->city, $record->state, $record->country, $record->zip_code,
                        ])))
                        ->placeholder('—')
                        ->columnSpanFull(),

                    TextEntry::make('customer_note')->label(__('pos.order.fields.customer_note'))->placeholder('—')->columnSpanFull(),
                    TextEntry::make('staff_note')->label(__('pos.order.fields.staff_note'))->placeholder('—')->columnSpanFull(),
                ]),
        ]);
    }
}

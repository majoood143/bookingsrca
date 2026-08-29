<?php

namespace App\Filament\Resources\PosTransactionResource\Pages;

use App\Filament\Resources\PosTransactionResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPosTransaction extends ViewRecord
{
    protected static string $resource = PosTransactionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(3)
                ->schema([
                    TextEntry::make('transaction_number')->label(__('pos.transaction.fields.transaction_number')),
                    TextEntry::make('terminal.name')->label(__('pos.transaction.fields.terminal')),
                    TextEntry::make('cashier.name')->label(__('pos.transaction.fields.cashier')),
                    TextEntry::make('status')
                        ->label(__('pos.transaction.fields.status'))
                        ->badge()
                        ->formatStateUsing(fn (string $state) => __('pos.transaction.status.' . $state)),

                    TextEntry::make('subtotal')->label(__('pos.transaction.fields.subtotal')),
                    TextEntry::make('discount_total')->label(__('pos.transaction.fields.discount_total')),
                    TextEntry::make('tax_total')->label(__('pos.transaction.fields.tax_total')),
                    TextEntry::make('grand_total')->label(__('pos.transaction.fields.grand_total'))->weight('bold'),
                    TextEntry::make('created_at')->label(__('pos.transaction.fields.created_at'))->dateTime(),

                    TextEntry::make('notes')
                        ->label(__('pos.transaction.fields.notes'))
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}

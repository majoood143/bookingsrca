<?php

namespace App\Filament\Resources\KioskCardResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('kiosk_card.transactions.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('kiosk_card.transactions.fields.date'))
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('kiosk_card.transactions.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state) => __('kiosk_card.transactions.types.' . $state))
                    ->colors([
                        'success' => 'topup',
                        'warning' => 'payment',
                        'info'    => 'refund',
                        'gray'    => 'adjustment',
                    ]),

                TextColumn::make('amount')
                    ->label(__('kiosk_card.transactions.fields.amount'))
                    ->money('OMR')
                    ->weight('bold')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->summarize(Sum::make()->money('OMR')->label(__('kiosk_card.transactions.total'))),

                TextColumn::make('balance_after')
                    ->label(__('kiosk_card.transactions.fields.balance_after'))
                    ->money('OMR'),

                TextColumn::make('kiosk.name')
                    ->label(__('kiosk_card.transactions.fields.kiosk'))
                    ->placeholder('-'),

                TextColumn::make('booking.booking_reference')
                    ->label(__('kiosk_card.transactions.fields.booking'))
                    ->placeholder('-'),

                TextColumn::make('recordedBy.name')
                    ->label(__('kiosk_card.transactions.fields.recorded_by'))
                    ->placeholder(__('kiosk_card.transactions.self_service')),

                TextColumn::make('reference')
                    ->label(__('kiosk_card.transactions.fields.reference'))
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

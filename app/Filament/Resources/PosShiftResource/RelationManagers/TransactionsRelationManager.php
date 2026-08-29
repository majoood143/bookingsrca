<?php

namespace App\Filament\Resources\PosShiftResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('pos.transaction.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->label(__('pos.transaction.fields.transaction_number')),

                TextColumn::make('status')
                    ->label(__('pos.transaction.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('pos.transaction.status.' . $state)),

                TextColumn::make('grand_total')
                    ->label(__('pos.transaction.fields.grand_total'))
                    ->html()
                    ->formatStateUsing(fn ($state) => view('partials.currency-amount', ['amount' => (float) $state])->render()),

                TextColumn::make('created_at')
                    ->label(__('pos.transaction.fields.created_at'))
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

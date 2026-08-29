<?php

namespace App\Filament\Resources\PosTransactionResource\RelationManagers;

use App\Models\BookingAttendee;
use App\Models\PosProduct;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Line Items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('itemable_type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === BookingAttendee::class ? __('Ticket') : __('Product'))
                    ->color(fn (string $state) => $state === BookingAttendee::class ? 'info' : 'gray'),

                TextColumn::make('description')
                    ->label(__('Description'))
                    ->getStateUsing(function ($record) {
                        if ($record->itemable_type === BookingAttendee::class) {
                            /** @var BookingAttendee|null $attendee */
                            $attendee = $record->itemable;

                            return $attendee?->ticketType?->getTranslation('name', app()->getLocale())
                                . ' — ' . $attendee?->getFullName();
                        }

                        /** @var PosProduct|null $product */
                        $product = $record->itemable;

                        return $product?->name;
                    }),

                TextColumn::make('quantity')->label(__('pos.screen.quantity')),

                TextColumn::make('unit_price')->label(__('Unit Price')),

                TextColumn::make('tax_amount')->label(__('pos.transaction.fields.tax_total')),

                TextColumn::make('line_total')->label(__('Line Total'))->weight('bold'),
            ]);
    }
}

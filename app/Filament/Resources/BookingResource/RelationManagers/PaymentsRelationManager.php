<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('booking.payments.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('payment_method')
                ->label(__('booking.payments.fields.method'))
                ->options(__('booking.payments.methods'))
                ->required()
                ->native(false),

            TextInput::make('amount')
                ->label(__('booking.payments.fields.amount'))
                ->numeric()
                ->prefix('OMR')
                ->required()
                ->minValue(0.01)
                ->default(fn() => $this->getOwnerRecord()->balance_due),

            TextInput::make('reference')
                ->label(__('booking.payments.fields.reference'))
                ->maxLength(255),

            Textarea::make('notes')
                ->label(__('booking.payments.fields.notes'))
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('booking.payments.fields.date'))
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label(__('booking.payments.fields.method'))
                    ->badge()
                    ->formatStateUsing(fn(string $state) => __('booking.payments.methods.' . $state))
                    ->colors([
                        'success' => 'cash',
                        'info'    => 'credit_debit',
                        'warning' => 'partial',
                    ]),

                TextColumn::make('amount')
                    ->label(__('booking.payments.fields.amount'))
                    ->money('OMR')
                    ->weight('bold')
                    ->summarize(Sum::make()->money('OMR')->label(__('booking.payments.total_recorded'))),

                TextColumn::make('reference')
                    ->label(__('booking.payments.fields.reference'))
                    ->placeholder('-'),

                TextColumn::make('recordedBy.name')
                    ->label(__('booking.payments.fields.recorded_by'))
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('booking.payments.record_payment'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['recorded_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

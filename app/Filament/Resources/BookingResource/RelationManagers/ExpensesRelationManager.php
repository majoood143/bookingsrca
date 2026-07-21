<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Enums\ExpensePaymentMethod;
use App\Enums\ExpensePaymentStatus;
use App\Enums\ExpenseType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('expense.navigation.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title.en')
                ->label(__('expense.fields.title_en'))
                ->required()
                ->maxLength(255),

            TextInput::make('title.ar')
                ->label(__('expense.fields.title_ar'))
                ->required()
                ->maxLength(255),

            TextInput::make('amount')
                ->label(__('expense.fields.amount'))
                ->numeric()
                ->required()
                ->minValue(0.001)
                ->step(0.001)
                ->suffix('OMR'),

            DatePicker::make('expense_date')
                ->label(__('expense.fields.expense_date'))
                ->required()
                ->default(now()),

            Select::make('payment_method')
                ->label(__('expense.fields.payment_method'))
                ->options(ExpensePaymentMethod::toArray())
                ->default(ExpensePaymentMethod::Cash->value)
                ->required()
                ->native(false),

            Select::make('payment_status')
                ->label(__('expense.fields.payment_status'))
                ->options(ExpensePaymentStatus::toArray())
                ->default(ExpensePaymentStatus::Paid->value)
                ->required()
                ->native(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_number')
                    ->label(__('expense.columns.number'))
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label(__('expense.columns.title'))
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale())),

                TextColumn::make('total_amount')
                    ->label(__('expense.columns.amount'))
                    ->money('OMR')
                    ->summarize(Sum::make()->money('OMR')),

                TextColumn::make('expense_date')
                    ->label(__('expense.columns.date'))
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('expense.columns.payment'))
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['expense_type'] = ExpenseType::Event->value;
                        $data['event_id'] = $this->getOwnerRecord()->event_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('expense_date', 'desc');
    }
}

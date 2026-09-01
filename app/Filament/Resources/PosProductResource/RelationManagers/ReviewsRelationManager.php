<?php

namespace App\Filament\Resources\PosProductResource\RelationManagers;

use App\Models\PosProductReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('pos.review.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.display_name')
                    ->label(__('pos.review.fields.customer')),

                TextColumn::make('rating')
                    ->label(__('pos.review.fields.rating'))
                    ->formatStateUsing(fn (int $state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state)),

                TextColumn::make('title')
                    ->label(__('pos.review.fields.title'))
                    ->placeholder('—'),

                TextColumn::make('body')
                    ->label(__('pos.review.fields.body'))
                    ->limit(80)
                    ->wrap(),

                IconColumn::make('is_approved')
                    ->label(__('pos.review.fields.is_approved'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label(__('pos.review.fields.is_approved')),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PosProductReview $record) => !$record->is_approved)
                    ->action(function (PosProductReview $record) {
                        $record->update(['is_approved' => true]);
                        Notification::make()->success()->title(__('pos.review.notifications.approved'))->send();
                    }),

                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageLogResource\Pages\ListMessageLogs;
use App\Models\MessageLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Settings → Message log: every WhatsApp and SMS the app tried to send a customer, to trace why a
 * code or voucher did not arrive. One-time code texts are never stored.
 */
class MessageLogResource extends Resource
{
    protected static ?string $model = MessageLog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 90;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getModelLabel(): string
    {
        return __('games.message_log.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('games.message_log.plural');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('games.message_log.fields.created_at'))
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),

                TextColumn::make('channel')
                    ->label(__('games.message_log.fields.channel'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->color(fn (string $state) => $state === 'whatsapp' ? 'success' : 'info'),

                TextColumn::make('type')
                    ->label(__('games.message_log.fields.type'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('recipient')
                    ->label(__('games.message_log.fields.recipient'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('games.message_log.fields.status'))
                    ->badge()
                    ->color(fn (string $state) => $state === 'sent' ? 'success' : 'danger'),

                TextColumn::make('message')
                    ->label(__('games.message_log.fields.message'))
                    ->placeholder(__('games.message_log.hidden'))
                    ->limit(60)
                    ->tooltip(fn (MessageLog $record) => $record->message),

                TextColumn::make('error')
                    ->label(__('games.message_log.fields.error'))
                    ->limit(60)
                    ->tooltip(fn (MessageLog $record) => $record->error)
                    ->color('danger')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label(__('games.message_log.fields.channel'))
                    ->options(['whatsapp' => 'WhatsApp', 'sms' => 'SMS']),

                SelectFilter::make('status')
                    ->label(__('games.message_log.fields.status'))
                    ->options(['sent' => 'sent', 'failed' => 'failed']),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageLogs::route('/'),
        ];
    }
}

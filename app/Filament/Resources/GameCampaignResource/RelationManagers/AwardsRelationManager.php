<?php

namespace App\Filament\Resources\GameCampaignResource\RelationManagers;

use App\Models\GameAward;
use App\Services\Games\GameAwardService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The winners of a finalized campaign and their vouchers: resend the message or void a voucher. */
class AwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'awards';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('games.award.plural');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['player', 'prize', 'promoCode']))
            ->columns([
                TextColumn::make('rank')
                    ->label(__('games.award.fields.rank'))
                    ->formatStateUsing(fn (int $state) => '#' . $state)
                    ->weight('bold'),

                TextColumn::make('player.name')
                    ->label(__('games.award.fields.player'))
                    ->description(fn (GameAward $record) => $record->player?->displayMobile()),

                TextColumn::make('prize')
                    ->label(__('games.award.fields.prize'))
                    ->getStateUsing(fn (GameAward $record) => $record->prize?->discountLabel()),

                TextColumn::make('promoCode.code')
                    ->label(__('games.award.fields.code'))
                    ->badge()
                    ->copyable()
                    ->description(fn (GameAward $record) => $record->promoCode?->valid_until?->format('d M Y')),

                IconColumn::make('redeemed')
                    ->label(__('games.award.fields.redeemed'))
                    ->getStateUsing(fn (GameAward $record) => ($record->promoCode?->used_count ?? 0) > 0)
                    ->boolean(),

                TextColumn::make('status')
                    ->label(__('games.award.fields.status'))
                    ->formatStateUsing(fn (string $state) => __('games.award.statuses.' . $state))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        GameAward::STATUS_NOTIFIED => 'success',
                        GameAward::STATUS_VOID => 'gray',
                        default => 'warning',
                    })
                    ->description(fn (GameAward $record) => $record->notify_error),

                TextColumn::make('notified_at')
                    ->label(__('games.award.fields.notified_at'))
                    ->dateTime('d M Y H:i')
                    ->description(fn (GameAward $record) => $record->notify_channel ? strtoupper($record->notify_channel) : null)
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label(__('games.award.actions.resend'))
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (GameAward $record) => $record->status !== GameAward::STATUS_VOID)
                    ->requiresConfirmation()
                    ->action(function (GameAward $record, GameAwardService $awards) {
                        $award = $awards->notify($record);

                        $award->notify_error === null && $award->notified_at
                            ? Notification::make()->success()->title(__('games.award.actions.resent'))->send()
                            : Notification::make()->danger()->title(__('games.award.actions.resend_failed', ['error' => $award->notify_error]))->send();
                    }),

                Action::make('void')
                    ->label(__('games.award.actions.void'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (GameAward $record) => $record->status !== GameAward::STATUS_VOID)
                    ->requiresConfirmation()
                    ->modalDescription(__('games.award.actions.void_desc'))
                    ->action(fn (GameAward $record, GameAwardService $awards) => $awards->void($record)),
            ])
            ->defaultSort('rank');
    }
}

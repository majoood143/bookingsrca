<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GamePlayerResource\Pages\ListGamePlayers;
use App\Filament\Resources\GamePlayerResource\Pages\ViewGamePlayer;
use App\Filament\Resources\GamePlayerResource\RelationManagers\AttemptsRelationManager;
use App\Models\BookingSetting;
use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Games → Players: the customers the games collected (verified mobile, name, consent), across all
 * campaigns, exportable for the client's CRM. Blocking keeps someone out of every game.
 */
class GamePlayerResource extends Resource
{
    protected static ?string $model = GamePlayer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) BookingSetting::get('module_games_enabled', true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('games.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('games.player.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('games.player.plural');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('attempts')
                ->withMax(['attempts as best_goals' => fn (Builder $q) => $q->where('status', GameAttempt::STATUS_COMPLETED)], 'goals'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('games.player.fields.name'))
                    ->searchable(),

                TextColumn::make('mobile')
                    ->label(__('games.player.fields.mobile'))
                    ->formatStateUsing(fn ($state, GamePlayer $record) => $record->displayMobile())
                    ->copyable()
                    ->copyableState(fn (GamePlayer $record) => '+' . $record->mobile)
                    ->searchable(),

                TextColumn::make('attempts_count')
                    ->label(__('games.player.fields.attempts'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('best_goals')
                    ->label(__('games.player.fields.best_goals'))
                    ->placeholder('—')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('marketing_opt_in')
                    ->label(__('games.player.fields.marketing_opt_in'))
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('locale')
                    ->label(__('games.player.fields.locale'))
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('blocked_at')
                    ->label(__('games.player.fields.blocked'))
                    ->getStateUsing(fn (GamePlayer $record) => $record->isBlocked())
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('verified_at')
                    ->label(__('games.player.fields.verified_at'))
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_seen_at')
                    ->label(__('games.player.fields.last_seen_at'))
                    ->since()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('games.player.fields.created_at'))
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('campaign')
                    ->label(__('games.player.filters.campaign'))
                    ->options(fn () => GameCampaign::query()->latest('starts_at')->get()->mapWithKeys(fn (GameCampaign $c) => [$c->id => $c->translated('name')])->all())
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('attempts', fn (Builder $q) => $q->where('game_campaign_id', $data['value']))
                        : $query),

                TernaryFilter::make('marketing_opt_in')
                    ->label(__('games.player.fields.marketing_opt_in')),

                TernaryFilter::make('blocked')
                    ->label(__('games.player.fields.blocked'))
                    ->nullable()
                    ->attribute('blocked_at'),
            ])
            ->recordActions([
                ViewAction::make(),
                ...self::blockActions(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function blockActions(): array
    {
        return [
            Action::make('block')
                ->label(__('games.player.actions.block'))
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (GamePlayer $record) => ! $record->isBlocked())
                ->authorize(fn (GamePlayer $record) => auth()->user()?->can('update', $record))
                ->modalDescription(__('games.player.actions.block_desc'))
                ->schema([
                    TextInput::make('blocked_reason')
                        ->label(__('games.player.fields.blocked_reason'))
                        ->maxLength(250),
                ])
                ->action(fn (GamePlayer $record, array $data) => $record->update([
                    'blocked_at' => now(),
                    'blocked_reason' => $data['blocked_reason'] ?? null,
                ])),

            Action::make('unblock')
                ->label(__('games.player.actions.unblock'))
                ->icon('heroicon-o-check')
                ->color('gray')
                ->visible(fn (GamePlayer $record) => $record->isBlocked())
                ->authorize(fn (GamePlayer $record) => auth()->user()?->can('update', $record))
                ->requiresConfirmation()
                ->action(fn (GamePlayer $record) => $record->update(['blocked_at' => null, 'blocked_reason' => null])),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    TextEntry::make('name')->label(__('games.player.fields.name')),
                    TextEntry::make('mobile')
                        ->label(__('games.player.fields.mobile'))
                        ->formatStateUsing(fn ($state, GamePlayer $record) => $record->displayMobile())
                        ->copyable(),
                    TextEntry::make('locale')->label(__('games.player.fields.locale'))->formatStateUsing(fn (string $state) => strtoupper($state)),
                    IconEntry::make('marketing_opt_in')->label(__('games.player.fields.marketing_opt_in'))->boolean(),
                    TextEntry::make('verified_at')->label(__('games.player.fields.verified_at'))->dateTime('d M Y H:i'),
                    TextEntry::make('terms_accepted_at')->label(__('games.player.fields.terms_accepted_at'))->dateTime('d M Y H:i'),
                    TextEntry::make('last_seen_at')->label(__('games.player.fields.last_seen_at'))->dateTime('d M Y H:i'),
                    TextEntry::make('last_ip')->label(__('games.player.fields.last_ip'))->placeholder('—'),
                    TextEntry::make('blocked_at')
                        ->label(__('games.player.fields.blocked_at'))
                        ->dateTime('d M Y H:i')
                        ->color('danger')
                        ->placeholder('—'),
                    TextEntry::make('blocked_reason')->label(__('games.player.fields.blocked_reason'))->placeholder('—')->columnSpan(3),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            AttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGamePlayers::route('/'),
            'view' => ViewGamePlayer::route('/{record}'),
        ];
    }
}

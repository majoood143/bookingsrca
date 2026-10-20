<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameAttemptResource\Pages\ListGameAttempts;
use App\Filament\Resources\GameAttemptResource\Pages\ViewGameAttempt;
use App\Models\BookingSetting;
use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Models\GameShot;
use App\Services\Games\LeaderboardService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Games → Attempts: every play-through with what the server decided and its fraud signals. The
 * navigation badge counts attempts waiting for review; clearing or invalidating them is logged.
 */
class GameAttemptResource extends Resource
{
    protected static ?string $model = GameAttempt::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 3;

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
        return __('games.attempt.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('games.attempt.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = GameAttempt::query()->where('review_status', GameAttempt::REVIEW_FLAGGED)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['player', 'campaign']))
            ->columns(self::columns(withCampaign: true))
            ->filters(self::filters(withCampaign: true))
            ->recordActions([ViewAction::make(), ...self::reviewActions()])
            ->defaultSort('started_at', 'desc');
    }

    /** @return array<int, \Filament\Tables\Columns\Column> */
    public static function columns(bool $withCampaign = false, bool $withPlayer = true): array
    {
        return array_values(array_filter([
            $withCampaign ? TextColumn::make('campaign.name')
                ->label(__('games.attempt.fields.campaign'))
                ->getStateUsing(fn (GameAttempt $record) => $record->campaign?->translated('name'))
                ->limit(30) : null,

            $withPlayer ? TextColumn::make('player.mobile')
                ->label(__('games.attempt.fields.player'))
                ->formatStateUsing(fn ($state, GameAttempt $record) => $record->player?->displayMobile())
                ->description(fn (GameAttempt $record) => $record->player?->name)
                ->searchable() : null,

            TextColumn::make('status')
                ->label(__('games.attempt.fields.status'))
                ->formatStateUsing(fn (string $state) => __('games.attempt.statuses.' . $state))
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    GameAttempt::STATUS_COMPLETED => 'success',
                    GameAttempt::STATUS_STARTED => 'info',
                    default => 'gray',
                }),

            TextColumn::make('goals')
                ->label(__('games.attempt.fields.goals'))
                ->formatStateUsing(fn (int $state, GameAttempt $record) => $state . '/' . $record->shots_taken)
                ->sortable(),

            TextColumn::make('duration_ms')
                ->label(__('games.attempt.fields.duration'))
                ->formatStateUsing(fn (?int $state) => GameAttempt::formatDuration($state))
                ->fontFamily('mono')
                ->sortable(),

            TextColumn::make('risk_score')
                ->label(__('games.attempt.fields.risk_score'))
                ->badge()
                ->color(fn (int $state) => $state >= 60 ? 'danger' : ($state > 0 ? 'warning' : 'gray'))
                ->sortable(),

            TextColumn::make('review_status')
                ->label(__('games.attempt.fields.review_status'))
                ->formatStateUsing(fn (?string $state) => __('games.attempt.review_statuses.' . ($state ?? 'none')))
                ->placeholder(__('games.attempt.review_statuses.none'))
                ->badge()
                ->color(fn (?string $state) => match ($state) {
                    GameAttempt::REVIEW_FLAGGED => 'danger',
                    GameAttempt::REVIEW_CLEARED => 'success',
                    GameAttempt::REVIEW_INVALIDATED => 'gray',
                    default => 'gray',
                }),

            TextColumn::make('started_at')
                ->label(__('games.attempt.fields.started_at'))
                ->dateTime('d M Y H:i')
                ->sortable(),
        ]));
    }

    /** @return array<int, \Filament\Tables\Filters\BaseFilter> */
    public static function filters(bool $withCampaign = false): array
    {
        return array_values(array_filter([
            $withCampaign ? SelectFilter::make('game_campaign_id')
                ->label(__('games.attempt.fields.campaign'))
                ->options(fn () => GameCampaign::query()->latest('starts_at')->get()->mapWithKeys(fn (GameCampaign $c) => [$c->id => $c->translated('name')])->all()) : null,

            SelectFilter::make('review_status')
                ->label(__('games.attempt.fields.review_status'))
                ->options([
                    GameAttempt::REVIEW_FLAGGED => __('games.attempt.review_statuses.flagged'),
                    GameAttempt::REVIEW_CLEARED => __('games.attempt.review_statuses.cleared'),
                    GameAttempt::REVIEW_INVALIDATED => __('games.attempt.review_statuses.invalidated'),
                ]),

            SelectFilter::make('status')
                ->label(__('games.attempt.fields.status'))
                ->options([
                    GameAttempt::STATUS_STARTED => __('games.attempt.statuses.started'),
                    GameAttempt::STATUS_COMPLETED => __('games.attempt.statuses.completed'),
                    GameAttempt::STATUS_ABANDONED => __('games.attempt.statuses.abandoned'),
                ]),
        ]));
    }

    /** Clear (counts) or invalidate (never counts) a completed attempt, with an optional note. */
    public static function reviewActions(): array
    {
        $review = fn (string $status) => function (GameAttempt $record, array $data) use ($status) {
            $record->update([
                'review_status' => $status,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
            ]);

            activity()
                ->performedOn($record)
                ->causedBy(auth()->user())
                ->withProperties(['review_status' => $status, 'note' => $data['review_note'] ?? null])
                ->log('game attempt ' . $status);

            app(LeaderboardService::class)->forget($record->campaign);

            Notification::make()->success()->title(__('games.attempt.actions.reviewed'))->send();
        };

        $note = [Textarea::make('review_note')->label(__('games.attempt.fields.review_note'))->maxLength(250)->rows(2)];

        return [
            Action::make('clear')
                ->label(__('games.attempt.actions.clear'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (GameAttempt $record) => $record->status === GameAttempt::STATUS_COMPLETED && $record->review_status !== GameAttempt::REVIEW_CLEARED)
                ->authorize(fn (GameAttempt $record) => auth()->user()?->can('update', $record))
                ->modalDescription(__('games.attempt.actions.clear_desc'))
                ->schema($note)
                ->action($review(GameAttempt::REVIEW_CLEARED)),

            Action::make('invalidate')
                ->label(__('games.attempt.actions.invalidate'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (GameAttempt $record) => $record->status === GameAttempt::STATUS_COMPLETED && $record->review_status !== GameAttempt::REVIEW_INVALIDATED)
                ->authorize(fn (GameAttempt $record) => auth()->user()?->can('update', $record))
                ->modalDescription(__('games.attempt.actions.invalidate_desc'))
                ->schema($note)
                ->action($review(GameAttempt::REVIEW_INVALIDATED)),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    TextEntry::make('campaign.name')
                        ->label(__('games.attempt.fields.campaign'))
                        ->getStateUsing(fn (GameAttempt $record) => $record->campaign?->translated('name')),
                    TextEntry::make('player')
                        ->label(__('games.attempt.fields.player'))
                        ->getStateUsing(fn (GameAttempt $record) => $record->player?->name . ' · ' . $record->player?->displayMobile())
                        ->url(fn (GameAttempt $record) => $record->player ? GamePlayerResource::getUrl('view', ['record' => $record->player]) : null),
                    TextEntry::make('goals')
                        ->label(__('games.attempt.fields.goals'))
                        ->formatStateUsing(fn (int $state, GameAttempt $record) => $state . '/' . $record->shots_taken),
                    TextEntry::make('duration_ms')
                        ->label(__('games.attempt.fields.duration'))
                        ->formatStateUsing(fn (?int $state) => GameAttempt::formatDuration($state))
                        ->placeholder('—'),
                    TextEntry::make('status')
                        ->label(__('games.attempt.fields.status'))
                        ->formatStateUsing(fn (string $state) => __('games.attempt.statuses.' . $state))
                        ->badge(),
                    TextEntry::make('review_status')
                        ->label(__('games.attempt.fields.review_status'))
                        ->formatStateUsing(fn (?string $state) => __('games.attempt.review_statuses.' . ($state ?? 'none')))
                        ->placeholder(__('games.attempt.review_statuses.none'))
                        ->badge(),
                    TextEntry::make('started_at')->label(__('games.attempt.fields.started_at'))->dateTime('d M Y H:i:s'),
                    TextEntry::make('finished_at')->label(__('games.attempt.fields.finished_at'))->dateTime('d M Y H:i:s')->placeholder('—'),
                ]),

            Section::make(__('games.attempt.fields.flags'))
                ->columns(4)
                ->schema([
                    TextEntry::make('risk_score')
                        ->label(__('games.attempt.fields.risk_score'))
                        ->badge()
                        ->color(fn (int $state) => $state >= 60 ? 'danger' : ($state > 0 ? 'warning' : 'success')),
                    TextEntry::make('flags')
                        ->label(__('games.attempt.fields.flags'))
                        ->getStateUsing(fn (GameAttempt $record) => self::flagSummary($record->flags ?? []))
                        ->placeholder('—')
                        ->columnSpan(3),
                    TextEntry::make('review_note')->label(__('games.attempt.fields.review_note'))->placeholder('—')->columnSpan(2),
                    TextEntry::make('reviewer.name')->label(__('games.attempt.fields.reviewed_by'))->placeholder('—'),
                    TextEntry::make('reviewed_at')->label(' ')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('ip')->label(__('games.attempt.fields.ip'))->placeholder('—'),
                    TextEntry::make('device_hash')->label(__('games.attempt.fields.device'))->limit(16)->placeholder('—'),
                    TextEntry::make('user_agent')->label(__('games.attempt.fields.user_agent'))->placeholder('—')->columnSpan(2),
                ]),

            Section::make(__('games.attempt.fields.shots'))
                ->schema([
                    RepeatableEntry::make('shots')
                        ->hiddenLabel()
                        ->columns(7)
                        ->schema([
                            TextEntry::make('n')->label(__('games.attempt.fields.shot')),
                            TextEntry::make('outcome')
                                ->label(__('games.attempt.fields.outcome'))
                                ->formatStateUsing(fn (string $state) => __('games.attempt.outcomes.' . $state))
                                ->badge()
                                ->color(fn (string $state) => $state === 'goal' ? 'success' : 'gray'),
                            TextEntry::make('aim')
                                ->label(__('games.attempt.fields.aim'))
                                ->getStateUsing(fn (GameShot $record) => number_format($record->aim_x, 2) . ', ' . number_format($record->aim_y, 2)),
                            TextEntry::make('power')->label(__('games.attempt.fields.power'))->numeric(2),
                            TextEntry::make('keeper')
                                ->label(__('games.attempt.fields.keeper'))
                                ->getStateUsing(fn (GameShot $record) => number_format($record->keeper['x'] ?? 0, 2) . ', ' . number_format($record->keeper['y'] ?? 0, 2)
                                    . (($record->keeper['read'] ?? false) ? ' · ' . __('games.attempt.fields.keeper_read') : '')),
                            TextEntry::make('gap_ms')
                                ->label(__('games.attempt.fields.gap'))
                                ->formatStateUsing(fn (?int $state) => $state === null ? '—' : number_format($state / 1000, 2) . ' s')
                                ->placeholder('—'),
                            TextEntry::make('signals')
                                ->label(__('games.attempt.fields.shot_signals'))
                                ->getStateUsing(fn (GameShot $record) => self::flagSummary($record->input['flags'] ?? []))
                                ->placeholder('—'),
                        ]),
                ]),
        ]);
    }

    /** "Perfectly straight swipe ×3, Shots faster than the game allows" */
    public static function flagSummary(array $flags): ?string
    {
        if ($flags === []) {
            return null;
        }

        return collect(array_count_values($flags))
            ->map(fn (int $count, string $flag) => __('games.attempt.flags.' . $flag) . ($count > 1 ? " ×{$count}" : ''))
            ->implode(', ');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGameAttempts::route('/'),
            'view' => ViewGameAttempt::route('/{record}'),
        ];
    }
}

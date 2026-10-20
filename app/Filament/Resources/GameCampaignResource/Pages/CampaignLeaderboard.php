<?php

namespace App\Filament\Resources\GameCampaignResource\Pages;

use App\Filament\Resources\GameAttemptResource;
use App\Filament\Resources\GameCampaignResource;
use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Services\Games\LeaderboardService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

/**
 * The live ranking for admins: every player's best eligible attempt with the full mobile number,
 * in the order prizes are given. The public board is the same list, masked (and maybe frozen).
 */
class CampaignLeaderboard extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = GameCampaignResource::class;

    protected string $view = 'filament.resources.game-campaign.leaderboard';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();
    }

    public function getTitle(): string
    {
        return __('games.campaign.leaderboard_page.title', ['name' => $this->campaign()->translated('name')]);
    }

    public function getSubheading(): ?string
    {
        return __('games.campaign.leaderboard_page.subtitle');
    }

    protected function getHeaderActions(): array
    {
        return [
            GameCampaignResource::finalizeAction()->record($this->campaign()),
            Action::make('public')
                ->label(__('games.campaign.actions.open_leaderboard'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url($this->campaign()->leaderboardUrl())
                ->openUrlInNewTab(),
        ];
    }

    public function table(Table $table): Table
    {
        $campaign = $this->campaign();
        $best = app(LeaderboardService::class)->bestAttempts($campaign)->select('attempt_id');

        return $table
            ->query(GameAttempt::query()
                ->whereIn('id', $best)
                ->with('player')
                ->orderByDesc('goals')
                ->orderBy('duration_ms')
                ->orderBy('finished_at')
                ->orderBy('id'))
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->weight('bold'),

                TextColumn::make('player.name')
                    ->label(__('games.player.fields.name'))
                    ->searchable(),

                TextColumn::make('player.mobile')
                    ->label(__('games.player.fields.mobile'))
                    ->formatStateUsing(fn ($state, GameAttempt $record) => $record->player->displayMobile())
                    ->copyable()
                    ->searchable(),

                TextColumn::make('goals')
                    ->label(__('games.attempt.fields.goals'))
                    ->formatStateUsing(fn (int $state) => $state . '/' . $campaign->shots_per_attempt)
                    ->weight('bold'),

                TextColumn::make('duration_ms')
                    ->label(__('games.attempt.fields.duration'))
                    ->formatStateUsing(fn (?int $state) => GameAttempt::formatDuration($state))
                    ->fontFamily('mono'),

                TextColumn::make('finished_at')
                    ->label(__('games.attempt.fields.finished_at'))
                    ->dateTime('d M Y H:i:s'),

                TextColumn::make('risk_score')
                    ->label(__('games.attempt.fields.risk_score'))
                    ->badge()
                    ->color(fn (int $state) => $state >= $campaign->risk_threshold ? 'danger' : ($state > 0 ? 'warning' : 'gray')),
            ])
            ->recordUrl(fn (GameAttempt $record) => GameAttemptResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                ExportAction::make()
                    ->label(__('games.player.actions.export'))
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => $campaign->slug . '-ranking-' . now()->format('Y-m-d-His')),
                    ]),
            ])
            ->paginated([25, 50, 100]);
    }

    private function campaign(): GameCampaign
    {
        /** @var GameCampaign */
        return $this->getRecord();
    }
}

<?php

namespace App\Filament\Resources\GameCampaignResource\Pages;

use App\Filament\Resources\GameCampaignResource;
use App\Models\GameCampaign;
use App\Services\Games\LeaderboardService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGameCampaign extends EditRecord
{
    protected static string $resource = GameCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            GameCampaignResource::finalizeAction(),
            Action::make('leaderboard')
                ->label(__('games.campaign.actions.leaderboard'))
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn (GameCampaign $record) => GameCampaignResource::getUrl('leaderboard', ['record' => $record])),
            Action::make('openGame')
                ->label(__('games.campaign.actions.open_game'))
                ->icon('heroicon-o-play')
                ->color('gray')
                ->url(fn (GameCampaign $record) => $record->playUrl())
                ->openUrlInNewTab(),
            GameCampaignResource::shareAction(),
            DeleteAction::make(),
        ];
    }

    // Size, freeze time or rule changes show on the public board straight away.
    protected function afterSave(): void
    {
        app(LeaderboardService::class)->forget($this->getRecord());
    }
}

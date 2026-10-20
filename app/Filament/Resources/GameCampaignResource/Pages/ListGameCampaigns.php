<?php

namespace App\Filament\Resources\GameCampaignResource\Pages;

use App\Filament\Resources\GameCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGameCampaigns extends ListRecords
{
    protected static string $resource = GameCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

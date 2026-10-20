<?php

namespace App\Filament\Resources\GameCampaignResource\RelationManagers;

use App\Filament\Resources\GameAttemptResource;
use App\Models\GameAttempt;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('games.attempt.plural');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('player'))
            ->columns(GameAttemptResource::columns())
            ->filters(GameAttemptResource::filters())
            ->recordUrl(fn (GameAttempt $record) => GameAttemptResource::getUrl('view', ['record' => $record]))
            ->recordActions(GameAttemptResource::reviewActions())
            ->defaultSort('started_at', 'desc');
    }
}

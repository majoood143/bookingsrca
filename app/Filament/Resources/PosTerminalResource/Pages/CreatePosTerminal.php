<?php

namespace App\Filament\Resources\PosTerminalResource\Pages;

use App\Filament\Resources\PosTerminalResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosTerminal extends CreateRecord
{
    protected static string $resource = PosTerminalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

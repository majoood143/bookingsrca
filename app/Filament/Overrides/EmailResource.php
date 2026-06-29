<?php

namespace App\Filament\Overrides;

use RickDBCN\FilamentEmail\Filament\Resources\EmailResource as BaseEmailResource;

class EmailResource extends BaseEmailResource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:Email') ?? false;
    }
}

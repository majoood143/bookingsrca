<?php
namespace App\Filament\Resources\Roles\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\RoleResource;


class RoleApiService extends ApiService
{
    protected static string | null $resource = RoleResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}

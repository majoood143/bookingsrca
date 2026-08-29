<?php
namespace App\Filament\Resources\CmsMenus\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CmsMenuResource;


class CmsMenuApiService extends ApiService
{
    protected static string | null $resource = CmsMenuResource::class;

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

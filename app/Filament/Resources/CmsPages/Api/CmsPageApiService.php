<?php
namespace App\Filament\Resources\CmsPages\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CmsPageResource;


class CmsPageApiService extends ApiService
{
    protected static string | null $resource = CmsPageResource::class;

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

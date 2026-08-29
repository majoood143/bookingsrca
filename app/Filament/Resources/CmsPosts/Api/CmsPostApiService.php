<?php
namespace App\Filament\Resources\CmsPosts\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CmsPostResource;


class CmsPostApiService extends ApiService
{
    protected static string | null $resource = CmsPostResource::class;

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

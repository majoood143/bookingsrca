<?php
namespace App\Filament\Resources\CmsTags\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CmsTagResource;


class CmsTagApiService extends ApiService
{
    protected static string | null $resource = CmsTagResource::class;

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

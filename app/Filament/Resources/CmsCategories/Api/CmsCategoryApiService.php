<?php
namespace App\Filament\Resources\CmsCategories\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CmsCategoryResource;


class CmsCategoryApiService extends ApiService
{
    protected static string | null $resource = CmsCategoryResource::class;

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

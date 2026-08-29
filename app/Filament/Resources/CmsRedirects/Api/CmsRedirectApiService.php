<?php
namespace App\Filament\Resources\CmsRedirects\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CmsRedirectResource;


class CmsRedirectApiService extends ApiService
{
    protected static string | null $resource = CmsRedirectResource::class;

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

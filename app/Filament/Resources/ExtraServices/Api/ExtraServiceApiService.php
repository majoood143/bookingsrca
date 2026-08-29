<?php
namespace App\Filament\Resources\ExtraServices\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\ExtraServiceResource;


class ExtraServiceApiService extends ApiService
{
    protected static string | null $resource = ExtraServiceResource::class;

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

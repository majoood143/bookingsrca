<?php
namespace App\Filament\Resources\KioskCards\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\KioskCardResource;


class KioskCardApiService extends ApiService
{
    protected static string | null $resource = KioskCardResource::class;

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

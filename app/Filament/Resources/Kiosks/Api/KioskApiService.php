<?php
namespace App\Filament\Resources\Kiosks\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\KioskResource;


class KioskApiService extends ApiService
{
    protected static string | null $resource = KioskResource::class;

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

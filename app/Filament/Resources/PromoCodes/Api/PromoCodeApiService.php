<?php
namespace App\Filament\Resources\PromoCodes\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\PromoCodeResource;


class PromoCodeApiService extends ApiService
{
    protected static string | null $resource = PromoCodeResource::class;

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

<?php
namespace App\Filament\Resources\TicketTypes\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\TicketTypeResource;


class TicketTypeApiService extends ApiService
{
    protected static string | null $resource = TicketTypeResource::class;

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

<?php
namespace App\Filament\Resources\TimeSlots\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\TimeSlotResource;


class TimeSlotApiService extends ApiService
{
    protected static string | null $resource = TimeSlotResource::class;

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

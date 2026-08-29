<?php
namespace App\Filament\Resources\BookingAttendees\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\BookingAttendeeResource;


class BookingAttendeeApiService extends ApiService
{
    protected static string | null $resource = BookingAttendeeResource::class;

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

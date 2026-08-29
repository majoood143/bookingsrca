<?php
namespace App\Filament\Resources\BookingSettings\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\BookingSettingResource;


class BookingSettingApiService extends ApiService
{
    protected static string | null $resource = BookingSettingResource::class;

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

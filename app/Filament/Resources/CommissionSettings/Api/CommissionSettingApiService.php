<?php
namespace App\Filament\Resources\CommissionSettings\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CommissionSettingResource;


class CommissionSettingApiService extends ApiService
{
    protected static string | null $resource = CommissionSettingResource::class;

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

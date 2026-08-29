<?php
namespace App\Filament\Resources\PaymentGatewayLogs\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\PaymentGatewayLogResource;


class PaymentGatewayLogApiService extends ApiService
{
    protected static string | null $resource = PaymentGatewayLogResource::class;

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

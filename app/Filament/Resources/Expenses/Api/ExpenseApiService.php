<?php
namespace App\Filament\Resources\Expenses\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\ExpenseResource;


class ExpenseApiService extends ApiService
{
    protected static string | null $resource = ExpenseResource::class;

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

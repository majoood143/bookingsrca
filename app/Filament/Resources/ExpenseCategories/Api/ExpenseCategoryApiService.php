<?php
namespace App\Filament\Resources\ExpenseCategories\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\ExpenseCategoryResource;


class ExpenseCategoryApiService extends ApiService
{
    protected static string | null $resource = ExpenseCategoryResource::class;

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

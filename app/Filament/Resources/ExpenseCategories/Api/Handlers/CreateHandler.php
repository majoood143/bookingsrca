<?php
namespace App\Filament\Resources\ExpenseCategories\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\ExpenseCategoryResource;
use App\Filament\Resources\ExpenseCategories\Api\Requests\CreateExpenseCategoryRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ExpenseCategoryResource::class;
    protected static string $permission = 'Create:ExpenseCategory';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create ExpenseCategory
     *
     * @param CreateExpenseCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateExpenseCategoryRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
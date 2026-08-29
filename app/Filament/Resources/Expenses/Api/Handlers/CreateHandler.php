<?php
namespace App\Filament\Resources\Expenses\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\Expenses\Api\Requests\CreateExpenseRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ExpenseResource::class;
    protected static string $permission = 'Create:Expense';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Expense
     *
     * @param CreateExpenseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateExpenseRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
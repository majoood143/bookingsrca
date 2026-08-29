<?php
namespace App\Filament\Resources\Expenses\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\Expenses\Api\Requests\UpdateExpenseRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = ExpenseResource::class;
    protected static string $permission = 'Update:Expense';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update Expense
     *
     * @param UpdateExpenseRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateExpenseRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
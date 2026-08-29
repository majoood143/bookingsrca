<?php
namespace App\Filament\Resources\PaymentGatewayLogs\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\PaymentGatewayLogResource;
use App\Filament\Resources\PaymentGatewayLogs\Api\Requests\UpdatePaymentGatewayLogRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = PaymentGatewayLogResource::class;
    protected static string $permission = 'Update:PaymentGatewayLog';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update PaymentGatewayLog
     *
     * @param UpdatePaymentGatewayLogRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdatePaymentGatewayLogRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
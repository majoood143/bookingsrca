<?php
namespace App\Filament\Resources\PaymentGatewayLogs\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\PaymentGatewayLogResource;
use App\Filament\Resources\PaymentGatewayLogs\Api\Requests\CreatePaymentGatewayLogRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = PaymentGatewayLogResource::class;
    protected static string $permission = 'Create:PaymentGatewayLog';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create PaymentGatewayLog
     *
     * @param CreatePaymentGatewayLogRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreatePaymentGatewayLogRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
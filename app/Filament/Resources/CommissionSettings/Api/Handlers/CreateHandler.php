<?php
namespace App\Filament\Resources\CommissionSettings\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CommissionSettingResource;
use App\Filament\Resources\CommissionSettings\Api\Requests\CreateCommissionSettingRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CommissionSettingResource::class;
    protected static string $permission = 'Create:CommissionSetting';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CommissionSetting
     *
     * @param CreateCommissionSettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCommissionSettingRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
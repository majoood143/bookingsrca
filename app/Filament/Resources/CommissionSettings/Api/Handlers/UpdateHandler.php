<?php
namespace App\Filament\Resources\CommissionSettings\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CommissionSettingResource;
use App\Filament\Resources\CommissionSettings\Api\Requests\UpdateCommissionSettingRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CommissionSettingResource::class;
    protected static string $permission = 'Update:CommissionSetting';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CommissionSetting
     *
     * @param UpdateCommissionSettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCommissionSettingRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
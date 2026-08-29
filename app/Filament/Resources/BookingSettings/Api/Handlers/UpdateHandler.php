<?php
namespace App\Filament\Resources\BookingSettings\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\BookingSettingResource;
use App\Filament\Resources\BookingSettings\Api\Requests\UpdateBookingSettingRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = BookingSettingResource::class;
    protected static string $permission = 'Update:BookingSetting';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update BookingSetting
     *
     * @param UpdateBookingSettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateBookingSettingRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
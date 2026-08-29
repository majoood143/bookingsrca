<?php
namespace App\Filament\Resources\BookingSettings\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\BookingSettingResource;
use App\Filament\Resources\BookingSettings\Api\Requests\CreateBookingSettingRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = BookingSettingResource::class;
    protected static string $permission = 'Create:BookingSetting';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create BookingSetting
     *
     * @param CreateBookingSettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateBookingSettingRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
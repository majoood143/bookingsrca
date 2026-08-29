<?php
namespace App\Filament\Resources\Kiosks\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\KioskResource;
use App\Filament\Resources\Kiosks\Api\Requests\CreateKioskRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = KioskResource::class;
    protected static string $permission = 'Create:Kiosk';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Kiosk
     *
     * @param CreateKioskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateKioskRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
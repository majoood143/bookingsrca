<?php
namespace App\Filament\Resources\Kiosks\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\KioskResource;
use App\Filament\Resources\Kiosks\Api\Requests\UpdateKioskRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = KioskResource::class;
    protected static string $permission = 'Update:Kiosk';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update Kiosk
     *
     * @param UpdateKioskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateKioskRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
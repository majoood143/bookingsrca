<?php
namespace App\Filament\Resources\ExtraServices\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\ExtraServiceResource;
use App\Filament\Resources\ExtraServices\Api\Requests\CreateExtraServiceRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ExtraServiceResource::class;
    protected static string $permission = 'Create:ExtraService';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create ExtraService
     *
     * @param CreateExtraServiceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateExtraServiceRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
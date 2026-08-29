<?php
namespace App\Filament\Resources\KioskCards\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\KioskCardResource;
use App\Filament\Resources\KioskCards\Api\Requests\CreateKioskCardRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = KioskCardResource::class;
    protected static string $permission = 'Create:KioskCard';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create KioskCard
     *
     * @param CreateKioskCardRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateKioskCardRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
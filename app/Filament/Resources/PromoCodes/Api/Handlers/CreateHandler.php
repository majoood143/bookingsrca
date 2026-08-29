<?php
namespace App\Filament\Resources\PromoCodes\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\PromoCodeResource;
use App\Filament\Resources\PromoCodes\Api\Requests\CreatePromoCodeRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = PromoCodeResource::class;
    protected static string $permission = 'Create:PromoCode';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create PromoCode
     *
     * @param CreatePromoCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreatePromoCodeRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
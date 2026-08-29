<?php
namespace App\Filament\Resources\PromoCodes\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\PromoCodeResource;
use App\Filament\Resources\PromoCodes\Api\Requests\UpdatePromoCodeRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = PromoCodeResource::class;
    protected static string $permission = 'Update:PromoCode';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update PromoCode
     *
     * @param UpdatePromoCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdatePromoCodeRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
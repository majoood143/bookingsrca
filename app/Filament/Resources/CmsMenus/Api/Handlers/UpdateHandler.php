<?php
namespace App\Filament\Resources\CmsMenus\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsMenuResource;
use App\Filament\Resources\CmsMenus\Api\Requests\UpdateCmsMenuRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsMenuResource::class;
    protected static string $permission = 'Update:CmsMenu';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CmsMenu
     *
     * @param UpdateCmsMenuRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCmsMenuRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
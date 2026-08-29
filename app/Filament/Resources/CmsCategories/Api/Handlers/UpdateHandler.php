<?php
namespace App\Filament\Resources\CmsCategories\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsCategoryResource;
use App\Filament\Resources\CmsCategories\Api\Requests\UpdateCmsCategoryRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsCategoryResource::class;
    protected static string $permission = 'Update:CmsCategory';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CmsCategory
     *
     * @param UpdateCmsCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCmsCategoryRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
<?php
namespace App\Filament\Resources\CmsPages\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsPageResource;
use App\Filament\Resources\CmsPages\Api\Requests\UpdateCmsPageRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsPageResource::class;
    protected static string $permission = 'Update:CmsPage';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CmsPage
     *
     * @param UpdateCmsPageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCmsPageRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
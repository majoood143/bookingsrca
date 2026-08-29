<?php
namespace App\Filament\Resources\CmsTags\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsTagResource;
use App\Filament\Resources\CmsTags\Api\Requests\UpdateCmsTagRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsTagResource::class;
    protected static string $permission = 'Update:CmsTag';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CmsTag
     *
     * @param UpdateCmsTagRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCmsTagRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
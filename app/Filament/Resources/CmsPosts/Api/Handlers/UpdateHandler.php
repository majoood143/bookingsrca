<?php
namespace App\Filament\Resources\CmsPosts\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsPostResource;
use App\Filament\Resources\CmsPosts\Api\Requests\UpdateCmsPostRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsPostResource::class;
    protected static string $permission = 'Update:CmsPost';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CmsPost
     *
     * @param UpdateCmsPostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCmsPostRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
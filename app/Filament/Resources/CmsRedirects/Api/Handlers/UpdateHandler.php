<?php
namespace App\Filament\Resources\CmsRedirects\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsRedirectResource;
use App\Filament\Resources\CmsRedirects\Api\Requests\UpdateCmsRedirectRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsRedirectResource::class;
    protected static string $permission = 'Update:CmsRedirect';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update CmsRedirect
     *
     * @param UpdateCmsRedirectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateCmsRedirectRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
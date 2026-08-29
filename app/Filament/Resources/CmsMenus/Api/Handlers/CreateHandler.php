<?php
namespace App\Filament\Resources\CmsMenus\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsMenuResource;
use App\Filament\Resources\CmsMenus\Api\Requests\CreateCmsMenuRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CmsMenuResource::class;
    protected static string $permission = 'Create:CmsMenu';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CmsMenu
     *
     * @param CreateCmsMenuRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCmsMenuRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
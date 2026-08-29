<?php
namespace App\Filament\Resources\CmsPages\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsPageResource;
use App\Filament\Resources\CmsPages\Api\Requests\CreateCmsPageRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CmsPageResource::class;
    protected static string $permission = 'Create:CmsPage';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CmsPage
     *
     * @param CreateCmsPageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCmsPageRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
<?php
namespace App\Filament\Resources\CmsTags\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsTagResource;
use App\Filament\Resources\CmsTags\Api\Requests\CreateCmsTagRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CmsTagResource::class;
    protected static string $permission = 'Create:CmsTag';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CmsTag
     *
     * @param CreateCmsTagRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCmsTagRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
<?php
namespace App\Filament\Resources\CmsPosts\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsPostResource;
use App\Filament\Resources\CmsPosts\Api\Requests\CreateCmsPostRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CmsPostResource::class;
    protected static string $permission = 'Create:CmsPost';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CmsPost
     *
     * @param CreateCmsPostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCmsPostRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
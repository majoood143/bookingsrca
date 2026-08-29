<?php
namespace App\Filament\Resources\CmsCategories\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsCategoryResource;
use App\Filament\Resources\CmsCategories\Api\Requests\CreateCmsCategoryRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CmsCategoryResource::class;
    protected static string $permission = 'Create:CmsCategory';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CmsCategory
     *
     * @param CreateCmsCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCmsCategoryRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
<?php
namespace App\Filament\Resources\CmsRedirects\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CmsRedirectResource;
use App\Filament\Resources\CmsRedirects\Api\Requests\CreateCmsRedirectRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CmsRedirectResource::class;
    protected static string $permission = 'Create:CmsRedirect';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create CmsRedirect
     *
     * @param CreateCmsRedirectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCmsRedirectRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
<?php
namespace App\Filament\Resources\Events\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\Events\Api\Requests\CreateEventRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = EventResource::class;
    protected static string $permission = 'Create:Event';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Event
     *
     * @param CreateEventRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateEventRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
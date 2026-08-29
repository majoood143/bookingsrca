<?php
namespace App\Filament\Resources\TicketTypes\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\TicketTypeResource;
use App\Filament\Resources\TicketTypes\Api\Requests\CreateTicketTypeRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = TicketTypeResource::class;
    protected static string $permission = 'Create:TicketType';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create TicketType
     *
     * @param CreateTicketTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateTicketTypeRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
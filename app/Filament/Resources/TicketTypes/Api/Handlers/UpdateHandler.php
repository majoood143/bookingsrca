<?php
namespace App\Filament\Resources\TicketTypes\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\TicketTypeResource;
use App\Filament\Resources\TicketTypes\Api\Requests\UpdateTicketTypeRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = TicketTypeResource::class;
    protected static string $permission = 'Update:TicketType';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update TicketType
     *
     * @param UpdateTicketTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateTicketTypeRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
<?php
namespace App\Filament\Resources\BookingAttendees\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\BookingAttendeeResource;
use App\Filament\Resources\BookingAttendees\Api\Requests\UpdateBookingAttendeeRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = BookingAttendeeResource::class;
    protected static string $permission = 'Update:BookingAttendee';

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update BookingAttendee
     *
     * @param UpdateBookingAttendeeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateBookingAttendeeRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}
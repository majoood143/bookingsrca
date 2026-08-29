<?php
namespace App\Filament\Resources\BookingAttendees\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\BookingAttendeeResource;
use App\Filament\Resources\BookingAttendees\Api\Requests\CreateBookingAttendeeRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = BookingAttendeeResource::class;
    protected static string $permission = 'Create:BookingAttendee';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create BookingAttendee
     *
     * @param CreateBookingAttendeeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateBookingAttendeeRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
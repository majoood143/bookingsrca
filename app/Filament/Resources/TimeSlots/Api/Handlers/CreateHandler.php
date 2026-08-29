<?php
namespace App\Filament\Resources\TimeSlots\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\TimeSlotResource;
use App\Filament\Resources\TimeSlots\Api\Requests\CreateTimeSlotRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = TimeSlotResource::class;
    protected static string $permission = 'Create:TimeSlot';

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create TimeSlot
     *
     * @param CreateTimeSlotRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateTimeSlotRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
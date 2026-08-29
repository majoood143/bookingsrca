<?php

namespace App\Filament\Resources\BookingAttendees\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\BookingAttendeeResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\BookingAttendees\Api\Transformers\BookingAttendeeTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = BookingAttendeeResource::class;
    protected static string $permission = 'View:BookingAttendee';


    /**
     * Show BookingAttendee
     *
     * @param Request $request
     * @return BookingAttendeeTransformer
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');
        
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )
            ->first();

        if (!$query) return static::sendNotFoundResponse();

        return new BookingAttendeeTransformer($query);
    }
}

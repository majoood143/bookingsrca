<?php

namespace App\Filament\Resources\Bookings\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\BookingResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\Bookings\Api\Transformers\BookingTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = BookingResource::class;
    protected static string $permission = 'View:Booking';


    /**
     * Show Booking
     *
     * @param Request $request
     * @return BookingTransformer
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

        return new BookingTransformer($query);
    }
}

<?php

namespace App\Filament\Resources\BookingSettings\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\BookingSettingResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\BookingSettings\Api\Transformers\BookingSettingTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = BookingSettingResource::class;
    protected static string $permission = 'View:BookingSetting';


    /**
     * Show BookingSetting
     *
     * @param Request $request
     * @return BookingSettingTransformer
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

        return new BookingSettingTransformer($query);
    }
}

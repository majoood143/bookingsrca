<?php

namespace App\Filament\Resources\TicketTypes\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\TicketTypeResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\TicketTypes\Api\Transformers\TicketTypeTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = TicketTypeResource::class;
    protected static string $permission = 'View:TicketType';


    /**
     * Show TicketType
     *
     * @param Request $request
     * @return TicketTypeTransformer
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

        return new TicketTypeTransformer($query);
    }
}

<?php

namespace App\Filament\Resources\Kiosks\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\KioskResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\Kiosks\Api\Transformers\KioskTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = KioskResource::class;
    protected static string $permission = 'View:Kiosk';


    /**
     * Show Kiosk
     *
     * @param Request $request
     * @return KioskTransformer
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

        return new KioskTransformer($query);
    }
}

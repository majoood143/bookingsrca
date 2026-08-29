<?php

namespace App\Filament\Resources\KioskCards\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\KioskCardResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\KioskCards\Api\Transformers\KioskCardTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = KioskCardResource::class;
    protected static string $permission = 'View:KioskCard';


    /**
     * Show KioskCard
     *
     * @param Request $request
     * @return KioskCardTransformer
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

        return new KioskCardTransformer($query);
    }
}

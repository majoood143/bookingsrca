<?php

namespace App\Filament\Resources\PromoCodes\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\PromoCodeResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\PromoCodes\Api\Transformers\PromoCodeTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = PromoCodeResource::class;
    protected static string $permission = 'View:PromoCode';


    /**
     * Show PromoCode
     *
     * @param Request $request
     * @return PromoCodeTransformer
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

        return new PromoCodeTransformer($query);
    }
}

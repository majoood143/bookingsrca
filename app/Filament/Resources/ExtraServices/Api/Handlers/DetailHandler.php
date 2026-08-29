<?php

namespace App\Filament\Resources\ExtraServices\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\ExtraServiceResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\ExtraServices\Api\Transformers\ExtraServiceTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = ExtraServiceResource::class;
    protected static string $permission = 'View:ExtraService';


    /**
     * Show ExtraService
     *
     * @param Request $request
     * @return ExtraServiceTransformer
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

        return new ExtraServiceTransformer($query);
    }
}

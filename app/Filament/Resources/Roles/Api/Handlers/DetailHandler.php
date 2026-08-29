<?php

namespace App\Filament\Resources\Roles\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\RoleResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\Roles\Api\Transformers\RoleTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = RoleResource::class;
    protected static string $permission = 'View:Role';


    /**
     * Show Role
     *
     * @param Request $request
     * @return RoleTransformer
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

        return new RoleTransformer($query);
    }
}

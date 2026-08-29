<?php

namespace App\Filament\Resources\CmsRedirects\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CmsRedirectResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CmsRedirects\Api\Transformers\CmsRedirectTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsRedirectResource::class;
    protected static string $permission = 'View:CmsRedirect';


    /**
     * Show CmsRedirect
     *
     * @param Request $request
     * @return CmsRedirectTransformer
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

        return new CmsRedirectTransformer($query);
    }
}

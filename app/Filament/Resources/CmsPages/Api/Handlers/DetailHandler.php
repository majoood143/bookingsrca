<?php

namespace App\Filament\Resources\CmsPages\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CmsPageResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CmsPages\Api\Transformers\CmsPageTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsPageResource::class;
    protected static string $permission = 'View:CmsPage';


    /**
     * Show CmsPage
     *
     * @param Request $request
     * @return CmsPageTransformer
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

        return new CmsPageTransformer($query);
    }
}

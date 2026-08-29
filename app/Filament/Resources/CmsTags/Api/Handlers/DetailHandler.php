<?php

namespace App\Filament\Resources\CmsTags\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CmsTagResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CmsTags\Api\Transformers\CmsTagTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsTagResource::class;
    protected static string $permission = 'View:CmsTag';


    /**
     * Show CmsTag
     *
     * @param Request $request
     * @return CmsTagTransformer
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

        return new CmsTagTransformer($query);
    }
}

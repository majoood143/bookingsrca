<?php

namespace App\Filament\Resources\CmsPosts\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CmsPostResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CmsPosts\Api\Transformers\CmsPostTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsPostResource::class;
    protected static string $permission = 'View:CmsPost';


    /**
     * Show CmsPost
     *
     * @param Request $request
     * @return CmsPostTransformer
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

        return new CmsPostTransformer($query);
    }
}

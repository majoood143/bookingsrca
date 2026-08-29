<?php

namespace App\Filament\Resources\CmsCategories\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CmsCategoryResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CmsCategories\Api\Transformers\CmsCategoryTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CmsCategoryResource::class;
    protected static string $permission = 'View:CmsCategory';


    /**
     * Show CmsCategory
     *
     * @param Request $request
     * @return CmsCategoryTransformer
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

        return new CmsCategoryTransformer($query);
    }
}

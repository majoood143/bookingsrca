<?php

namespace App\Filament\Resources\CommissionSettings\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CommissionSettingResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CommissionSettings\Api\Transformers\CommissionSettingTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CommissionSettingResource::class;
    protected static string $permission = 'View:CommissionSetting';


    /**
     * Show CommissionSetting
     *
     * @param Request $request
     * @return CommissionSettingTransformer
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

        return new CommissionSettingTransformer($query);
    }
}

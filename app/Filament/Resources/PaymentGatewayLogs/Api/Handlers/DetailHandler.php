<?php

namespace App\Filament\Resources\PaymentGatewayLogs\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\PaymentGatewayLogResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\PaymentGatewayLogs\Api\Transformers\PaymentGatewayLogTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = PaymentGatewayLogResource::class;
    protected static string $permission = 'View:PaymentGatewayLog';


    /**
     * Show PaymentGatewayLog
     *
     * @param Request $request
     * @return PaymentGatewayLogTransformer
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

        return new PaymentGatewayLogTransformer($query);
    }
}

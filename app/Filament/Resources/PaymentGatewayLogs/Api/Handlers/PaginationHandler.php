<?php
namespace App\Filament\Resources\PaymentGatewayLogs\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Resources\PaymentGatewayLogResource;
use App\Filament\Resources\PaymentGatewayLogs\Api\Transformers\PaymentGatewayLogTransformer;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = PaymentGatewayLogResource::class;
    protected static string $permission = 'ViewAny:PaymentGatewayLog';


    /**
     * List of PaymentGatewayLog
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler()
    {
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for($query)
        ->allowedFields($this->getAllowedFields() ?? [])
        ->allowedSorts($this->getAllowedSorts() ?? [])
        ->allowedFilters($this->getAllowedFilters() ?? [])
        ->allowedIncludes($this->getAllowedIncludes() ?? [])
        ->paginate(request()->query('per_page'))
        ->appends(request()->query());

        return PaymentGatewayLogTransformer::collection($query);
    }
}

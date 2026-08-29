<?php
namespace App\Filament\Resources\ExpenseCategories\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use App\Filament\Resources\ExpenseCategoryResource;
use App\Filament\Resources\ExpenseCategories\Api\Transformers\ExpenseCategoryTransformer;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ExpenseCategoryResource::class;
    protected static string $permission = 'ViewAny:ExpenseCategory';


    /**
     * List of ExpenseCategory
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

        return ExpenseCategoryTransformer::collection($query);
    }
}

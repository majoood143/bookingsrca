<?php

namespace App\Filament\Resources\ExpenseCategories\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\ExpenseCategoryResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\ExpenseCategories\Api\Transformers\ExpenseCategoryTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = ExpenseCategoryResource::class;
    protected static string $permission = 'View:ExpenseCategory';


    /**
     * Show ExpenseCategory
     *
     * @param Request $request
     * @return ExpenseCategoryTransformer
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

        return new ExpenseCategoryTransformer($query);
    }
}

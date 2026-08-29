<?php

namespace App\Filament\Resources\Expenses\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\ExpenseResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\Expenses\Api\Transformers\ExpenseTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = ExpenseResource::class;
    protected static string $permission = 'View:Expense';


    /**
     * Show Expense
     *
     * @param Request $request
     * @return ExpenseTransformer
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

        return new ExpenseTransformer($query);
    }
}

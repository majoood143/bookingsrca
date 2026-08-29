<?php
namespace App\Filament\Resources\Expenses\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Expense;

/**
 * @property Expense $resource
 */
class ExpenseTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}

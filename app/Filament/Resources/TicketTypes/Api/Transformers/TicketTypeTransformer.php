<?php
namespace App\Filament\Resources\TicketTypes\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TicketType;

/**
 * @property TicketType $resource
 */
class TicketTypeTransformer extends JsonResource
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

<?php
namespace App\Filament\Resources\Events\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Event;

/**
 * @property Event $resource
 */
class EventTransformer extends JsonResource
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

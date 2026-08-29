<?php
namespace App\Filament\Resources\ExtraServices\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ExtraService;

/**
 * @property ExtraService $resource
 */
class ExtraServiceTransformer extends JsonResource
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

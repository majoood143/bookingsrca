<?php
namespace App\Filament\Resources\PromoCodes\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PromoCode;

/**
 * @property PromoCode $resource
 */
class PromoCodeTransformer extends JsonResource
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

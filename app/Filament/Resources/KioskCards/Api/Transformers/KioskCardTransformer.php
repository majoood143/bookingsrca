<?php
namespace App\Filament\Resources\KioskCards\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\KioskCard;

/**
 * @property KioskCard $resource
 */
class KioskCardTransformer extends JsonResource
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

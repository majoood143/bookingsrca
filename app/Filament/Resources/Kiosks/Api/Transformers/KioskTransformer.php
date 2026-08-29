<?php
namespace App\Filament\Resources\Kiosks\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Kiosk;

/**
 * @property Kiosk $resource
 */
class KioskTransformer extends JsonResource
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

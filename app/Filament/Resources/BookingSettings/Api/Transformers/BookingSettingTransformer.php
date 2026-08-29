<?php
namespace App\Filament\Resources\BookingSettings\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BookingSetting;

/**
 * @property BookingSetting $resource
 */
class BookingSettingTransformer extends JsonResource
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

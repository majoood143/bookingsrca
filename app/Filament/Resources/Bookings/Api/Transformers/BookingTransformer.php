<?php
namespace App\Filament\Resources\Bookings\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Booking;

/**
 * @property Booking $resource
 */
class BookingTransformer extends JsonResource
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

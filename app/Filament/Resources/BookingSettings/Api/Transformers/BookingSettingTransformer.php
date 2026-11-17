<?php

namespace App\Filament\Resources\BookingSettings\Api\Transformers;

use App\Models\BookingSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property BookingSetting $resource
 */
class BookingSettingTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    // Gateway credentials and tokens are never sent back over the API: one leaked
    // API token with view access must not hand out the payment keys.
    private const SECRET_PATTERN = '/(secret|password|_token$|working_key|resource_key|access_code)/';

    public function toArray($request)
    {
        $data = $this->resource->toArray();

        if (preg_match(self::SECRET_PATTERN, (string) $this->resource->key) && filled($data['value'] ?? null)) {
            $data['value'] = '********';
        }

        return $data;
    }
}

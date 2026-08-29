<?php
namespace App\Filament\Resources\CommissionSettings\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CommissionSetting;

/**
 * @property CommissionSetting $resource
 */
class CommissionSettingTransformer extends JsonResource
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

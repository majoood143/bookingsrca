<?php
namespace App\Filament\Resources\CmsPages\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CmsPage;

/**
 * @property CmsPage $resource
 */
class CmsPageTransformer extends JsonResource
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

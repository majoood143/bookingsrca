<?php
namespace App\Filament\Resources\CmsCategories\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CmsCategory;

/**
 * @property CmsCategory $resource
 */
class CmsCategoryTransformer extends JsonResource
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

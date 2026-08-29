<?php
namespace App\Filament\Resources\CmsPosts\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CmsPost;

/**
 * @property CmsPost $resource
 */
class CmsPostTransformer extends JsonResource
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

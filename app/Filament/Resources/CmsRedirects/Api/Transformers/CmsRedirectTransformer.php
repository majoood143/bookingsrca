<?php
namespace App\Filament\Resources\CmsRedirects\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CmsRedirect;

/**
 * @property CmsRedirect $resource
 */
class CmsRedirectTransformer extends JsonResource
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

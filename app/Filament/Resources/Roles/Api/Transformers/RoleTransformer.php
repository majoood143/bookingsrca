<?php
namespace App\Filament\Resources\Roles\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Role;

/**
 * @property Role $resource
 */
class RoleTransformer extends JsonResource
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

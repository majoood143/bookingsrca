<?php

namespace App\Filament\Resources\CmsCategories\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'name' => 'required',
			'slug' => 'required',
			'description' => 'required',
			'parent_id' => 'required',
			'order' => 'required',
			'deleted_at' => 'required'
		];
    }
}

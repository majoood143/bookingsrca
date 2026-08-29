<?php

namespace App\Filament\Resources\CmsPosts\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsPostRequest extends FormRequest
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
			'title' => 'required',
			'slug' => 'required',
			'excerpt' => 'required',
			'content' => 'required',
			'meta_title' => 'required',
			'meta_description' => 'required',
			'template' => 'required',
			'container_width' => 'required',
			'show_title' => 'required',
			'custom_css' => 'required|string',
			'status' => 'required',
			'published_at' => 'required',
			'canonical_url' => 'required',
			'noindex' => 'required',
			'nofollow' => 'required',
			'og_type' => 'required',
			'category_id' => 'required',
			'user_id' => 'required',
			'deleted_at' => 'required'
		];
    }
}

<?php

namespace App\Filament\Resources\CmsRedirects\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCmsRedirectRequest extends FormRequest
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
			'from_path' => 'required',
			'to_path' => 'required',
			'status_code' => 'required',
			'hits' => 'required'
		];
    }
}

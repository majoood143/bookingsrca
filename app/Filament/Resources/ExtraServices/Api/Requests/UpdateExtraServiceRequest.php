<?php

namespace App\Filament\Resources\ExtraServices\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExtraServiceRequest extends FormRequest
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
			'event_id' => 'required',
			'name' => 'required',
			'description' => 'required',
			'price' => 'required|numeric',
			'quantity_available' => 'required',
			'quantity_used' => 'required',
			'is_active' => 'required'
		];
    }
}

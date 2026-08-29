<?php

namespace App\Filament\Resources\CommissionSettings\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommissionSettingRequest extends FormRequest
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
			'commission_type' => 'required',
			'commission_value' => 'required|numeric',
			'effective_from' => 'required|date',
			'effective_to' => 'required|date',
			'is_active' => 'required'
		];
    }
}

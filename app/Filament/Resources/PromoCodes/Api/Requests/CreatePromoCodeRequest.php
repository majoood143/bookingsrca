<?php

namespace App\Filament\Resources\PromoCodes\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePromoCodeRequest extends FormRequest
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
			'code' => 'required',
			'name' => 'required',
			'description' => 'required',
			'discount_type' => 'required',
			'discount_value' => 'required|numeric',
			'max_uses' => 'required',
			'used_count' => 'required',
			'valid_from' => 'required',
			'valid_until' => 'required',
			'is_active' => 'required',
			'event_id' => 'required',
			'deleted_at' => 'required'
		];
    }
}

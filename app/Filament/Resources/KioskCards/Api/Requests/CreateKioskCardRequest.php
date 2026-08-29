<?php

namespace App\Filament\Resources\KioskCards\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateKioskCardRequest extends FormRequest
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
			'uid' => 'required',
			'holder_name' => 'required',
			'phone' => 'required',
			'balance' => 'required|numeric',
			'status' => 'required',
			'notes' => 'required|string'
		];
    }
}

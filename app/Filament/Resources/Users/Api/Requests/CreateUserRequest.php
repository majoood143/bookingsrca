<?php

namespace App\Filament\Resources\Users\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
			'prefix' => 'required',
			'email' => 'required',
			'email_verified_at' => 'required',
			'is_active' => 'required',
			'password' => 'required',
			'remember_token' => 'required',
			'custom_fields' => 'required',
			'avatar_url' => 'required',
			'date_of_birth' => 'required|date',
			'gender' => 'required',
			'marital_status' => 'required',
			'blood_group' => 'required',
			'mobile_number' => 'required',
			'guardian_name' => 'required',
			'nationality' => 'required',
			'national_id_number' => 'required',
			'address' => 'required|string',
			'bank_account_holder_name' => 'required',
			'bank_account_number' => 'required',
			'bank_name' => 'required',
			'bank_identifier_code' => 'required',
			'bank_branch' => 'required'
		];
    }
}

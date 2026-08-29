<?php

namespace App\Filament\Resources\PaymentGatewayLogs\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentGatewayLogRequest extends FormRequest
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
			'booking_id' => 'required',
			'gateway' => 'required',
			'event' => 'required',
			'status_code' => 'required',
			'request_payload' => 'required',
			'response_payload' => 'required'
		];
    }
}

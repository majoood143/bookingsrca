<?php

namespace App\Filament\Resources\BookingAttendees\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingAttendeeRequest extends FormRequest
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
			'ticket_type_id' => 'required',
			'ticket_price' => 'required|numeric',
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required',
			'phone' => 'required',
			'date_of_birth' => 'required|date',
			'gender' => 'required',
			'nationality' => 'required',
			'identity_number' => 'required',
			'passport_number' => 'required',
			'identity_card_path' => 'required',
			'passport_path' => 'required',
			'ticket_number' => 'required',
			'qr_code' => 'required',
			'pdf_path' => 'required',
			'email_sent' => 'required',
			'email_sent_at' => 'required',
			'checked_in' => 'required',
			'checked_in_at' => 'required'
		];
    }
}

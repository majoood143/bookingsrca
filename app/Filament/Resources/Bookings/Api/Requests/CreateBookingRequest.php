<?php

namespace App\Filament\Resources\Bookings\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
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
			'booking_reference' => 'required',
			'event_id' => 'required',
			'time_slot_id' => 'required',
			'ticket_type_id' => 'required',
			'event_date' => 'required|date',
			'quantity' => 'required',
			'ticket_price' => 'required|numeric',
			'services_price' => 'required|numeric',
			'total_price' => 'required|numeric',
			'promo_code_id' => 'required',
			'discount_amount' => 'required|numeric',
			'source' => 'required',
			'locale' => 'required',
			'ip_address' => 'required',
			'user_agent' => 'required|string',
			'created_by' => 'required',
			'kiosk_id' => 'required',
			'status' => 'required',
			'payment_method' => 'required',
			'payment_status' => 'required',
			'payment_session_id' => 'required',
			'payment_reference' => 'required',
			'qr_code' => 'required',
			'confirmed_at' => 'required',
			'cancelled_at' => 'required',
			'refunded_at' => 'required',
			'refunded_by' => 'required',
			'refund_reason' => 'required|string'
		];
    }
}

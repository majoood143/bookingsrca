<?php

namespace App\Filament\Resources\TimeSlots\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotRequest extends FormRequest
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
			'date' => 'required|date',
			'start_time' => 'required',
			'end_time' => 'required',
			'label' => 'required',
			'max_attendees' => 'required',
			'current_bookings' => 'required',
			'is_active' => 'required',
			'booking_channel' => 'required'
		];
    }
}

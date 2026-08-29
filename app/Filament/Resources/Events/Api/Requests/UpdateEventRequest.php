<?php

namespace App\Filament\Resources\Events\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
			'title' => 'required',
			'description' => 'required',
			'timeline' => 'required',
			'faq' => 'required',
			'terms_and_conditions' => 'required',
			'terms_url' => 'required',
			'admission_policies' => 'required',
			'promotional_video_url' => 'required',
			'slug' => 'required',
			'location' => 'required',
			'location_link' => 'required',
			'organizer' => 'required',
			'organizer_phone' => 'required',
			'start_date' => 'required|date',
			'end_date' => 'required|date',
			'is_recurring' => 'required',
			'recurring_days' => 'required',
			'image' => 'required',
			'status' => 'required',
			'password' => 'required',
			'max_attendees' => 'required',
			'booking_cutoff_enabled' => 'required',
			'booking_cutoff_value' => 'required',
			'booking_cutoff_unit' => 'required'
		];
    }
}

<?php

namespace App\Filament\Resources\Kiosks\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKioskRequest extends FormRequest
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
			'code' => 'required',
			'event_id' => 'required',
			'override_min_tickets_per_booking' => 'required',
			'override_max_tickets_per_booking' => 'required',
			'override_max_attendee_age_years' => 'required',
			'override_pending_booking_expiry_minutes' => 'required',
			'override_show_slot_end_time' => 'required',
			'min_tickets_per_booking' => 'required',
			'max_tickets_per_booking' => 'required',
			'max_attendee_age_years' => 'required',
			'pending_booking_expiry_minutes' => 'required',
			'show_slot_end_time' => 'required',
			'override_booking_cutoff' => 'required',
			'booking_cutoff_enabled' => 'required',
			'booking_cutoff_value' => 'required',
			'booking_cutoff_unit' => 'required',
			'override_capacity_enforcement' => 'required',
			'enforce_capacity' => 'required',
			'is_active' => 'required',
			'idle_timeout_seconds' => 'required',
			'enabled_payment_methods' => 'required',
			'receipt_footer_text' => 'required',
			'slideshow_images' => 'required',
			'reader_connected' => 'required',
			'reader_last_seen_at' => 'required',
			'printer_connected' => 'required',
			'printer_last_seen_at' => 'required',
			'app_version' => 'required'
		];
    }
}

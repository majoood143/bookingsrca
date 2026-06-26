<?php

use App\Models\BookingSetting;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function rules()
    {
        $minTickets = BookingSetting::get('min_tickets_per_booking', 1);
        $maxTickets = BookingSetting::get('max_tickets_per_booking', 10);

        return [
            'quantity' => "required|integer|min:{$minTickets}|max:{$maxTickets}",
            // ... other rules
        ];
    }

    public function messages()
    {
        $maxTickets = BookingSetting::get('max_tickets_per_booking', 10);

        return [
            'quantity.max' => "Maximum {$maxTickets} tickets allowed per booking.",
            // ... other messages
        ];
    }
}
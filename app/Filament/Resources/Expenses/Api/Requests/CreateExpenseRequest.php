<?php

namespace App\Filament\Resources\Expenses\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExpenseRequest extends FormRequest
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
			'expense_number' => 'required',
			'event_id' => 'required',
			'booking_id' => 'required',
			'category_id' => 'required',
			'expense_type' => 'required',
			'title' => 'required',
			'description' => 'required',
			'amount' => 'required|numeric',
			'currency' => 'required',
			'tax_amount' => 'required|numeric',
			'total_amount' => 'required|numeric',
			'payment_method' => 'required',
			'payment_status' => 'required',
			'payment_reference' => 'required',
			'expense_date' => 'required|date',
			'due_date' => 'required|date',
			'paid_at' => 'required',
			'vendor_name' => 'required',
			'vendor_phone' => 'required',
			'vendor_email' => 'required',
			'attachments' => 'required',
			'is_recurring' => 'required',
			'recurring_frequency' => 'required',
			'recurring_start_date' => 'required|date',
			'recurring_end_date' => 'required|date',
			'recurring_count' => 'required',
			'parent_expense_id' => 'required',
			'status' => 'required',
			'notes' => 'required|string',
			'rejection_reason' => 'required|string',
			'created_by' => 'required',
			'approved_by' => 'required',
			'approved_at' => 'required',
			'deleted_at' => 'required'
		];
    }
}

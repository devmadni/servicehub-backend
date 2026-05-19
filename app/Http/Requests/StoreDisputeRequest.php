<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => 'required|integer|exists:bookings,id',
            'trigger_type' => 'required|in:no_show,late,quality,price,damage,refund',
            'description' => 'required|string|min:10|max:2000',
        ];
    }
}

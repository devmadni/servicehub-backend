<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'provider_id' => 'required|integer|exists:providers,id',
            'service_request_id' => 'required|integer|exists:service_requests,id',
            'pricing_quote_id' => 'nullable|integer|exists:pricing_quotes,id',
            'slot_datetime' => 'required|date|after:now',
        ];
    }
}

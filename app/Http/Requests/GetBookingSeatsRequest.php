<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetBookingSeatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'concert_ticket_type_id' => ['required', 'integer', 'min:1', 'exists:concert_ticket_options,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'concert_ticket_type_id.required' => 'Ticket option is required.',
            'concert_ticket_type_id.exists' => 'Invalid ticket option.',
        ];
    }
}

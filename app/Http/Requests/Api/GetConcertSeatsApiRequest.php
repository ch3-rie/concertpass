<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\ApiFormRequest;

class GetConcertSeatsApiRequest extends ApiFormRequest
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

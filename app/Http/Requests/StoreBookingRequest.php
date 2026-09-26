<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DecodesBookingItems;
use App\Models\Concert;
use App\Services\BookingValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    use DecodesBookingItems;

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
            'booking_items' => ['required', 'string', 'json'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_items.required' => 'Please select at least one ticket.',
            'booking_items.json' => 'Invalid booking data.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->decodedBookingItems();
            if ($items === [] && $this->filled('booking_items')) {
                $validator->errors()->add('booking_items', 'Invalid booking data.');

                return;
            }

            /** @var Concert $concert */
            $concert = $this->route('concert');
            $error = app(BookingValidationService::class)->validateBookingItems($concert, $items);
            if ($error !== null) {
                $validator->errors()->add('booking_items', $error);
            }
        });
    }
}

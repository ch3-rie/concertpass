<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DecodesBookingItems;
use App\Models\Concert;
use App\Services\BookingValidationService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmBookingPaymentRequest extends FormRequest
{
    use DecodesBookingItems;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cardRaw = (string) $this->input('card_number', '');
        $cardDigits = preg_replace('/[\s-]+/', '', $cardRaw) ?? '';

        $cvvRaw = (string) $this->input('cvv', '');
        $cvvDigits = preg_replace('/\D+/', '', $cvvRaw) ?? '';

        $expRaw = (string) $this->input('expiry', '');
        $expDigits = preg_replace('/\D+/', '', $expRaw) ?? '';
        $expiryNormalized = $expDigits;
        if (strlen($expDigits) === 4) {
            $expiryNormalized = substr($expDigits, 0, 2).'/'.substr($expDigits, 2, 2);
        }

        $this->merge([
            'card_number' => $cardDigits,
            'cvv' => $cvvDigits,
            'expiry' => $expiryNormalized,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->decodedBookingItems();
            if ($items === []) {
                $validator->errors()->add('booking_items', 'Please select at least one ticket.');
            } else {
                /** @var Concert $concert */
                $concert = $this->route('concert');
                $bookingError = app(BookingValidationService::class)->validateBookingItems($concert, $items);
                if ($bookingError !== null) {
                    $validator->errors()->add('booking_items', $bookingError);
                }
            }

            $expiry = (string) $this->input('expiry', '');
            if ($expiry !== '') {
                if (! preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
                    $validator->errors()->add(
                        'expiry',
                        'Enter expiry as MM/YY.'
                    );
                } elseif ($this->cardExpiryIsInThePast($expiry)) {
                    $validator->errors()->add(
                        'expiry',
                        'This card has expired. Enter the expiry month and year shown on your card (MM/YY).'
                    );
                }
            }
        });
    }

    /**
     * Cards remain valid through the last day of the expiry month (industry standard).
     */
    private function cardExpiryIsInThePast(string $mmYy): bool
    {
        [$mm, $yy] = array_map('intval', explode('/', $mmYy, 2));
        $fullYear = 2000 + ($yy % 100);
        $lastCalendarDay = Carbon::create($fullYear, $mm, 1)->endOfMonth()->toDateString();
        $today = Carbon::today()->toDateString();

        return $today > $lastCalendarDay;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'booking_items' => ['required', 'string', 'json'],
            'card_number' => ['required', 'string', 'regex:/^[0-9]{12,19}$/'],
            'expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'cardholder_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}\s\-\'.]+$/u'],
            'terms' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_items.json' => 'Invalid booking data.',
            'card_number.required' => 'Please enter your card number.',
            'card_number.regex' => 'Card number must be 12–19 digits only (no letters or symbols).',
            'expiry.required' => 'Please enter your card expiry date.',
            'expiry.regex' => 'Enter expiry as MM/YY.',
            'cvv.regex' => 'Enter a valid CVV.',
            'cardholder_name.regex' => 'Enter the name as shown on the card.',
            'terms.accepted' => 'You must agree to the terms before completing your purchase.',
        ];
    }
}

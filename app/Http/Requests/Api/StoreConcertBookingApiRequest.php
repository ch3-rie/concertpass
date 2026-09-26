<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Concerns\DecodesBookingItems;
use App\Models\Concert;
use App\Services\BookingValidationService;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class StoreConcertBookingApiRequest extends ApiFormRequest
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

            $card = (string) $this->input('card_number', '');
            if ($card !== '' && preg_match('/^[0-9]{12,19}$/', $card) && ! $this->cardNumberPassesLuhn($card)) {
                $validator->errors()->add(
                    'card_number',
                    'This card number is not valid. Check the digits and try again.'
                );
            }

            $expiry = (string) $this->input('expiry', '');
            if ($expiry !== '' && preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
                if ($this->cardExpiryIsInThePast($expiry)) {
                    $validator->errors()->add(
                        'expiry',
                        'This card has expired. Enter the expiry month and year shown on your card (MM/YY).'
                    );
                }
            }
        });
    }

    private function cardNumberPassesLuhn(string $digitsOnly): bool
    {
        $sum = 0;
        $alternate = false;
        for ($i = strlen($digitsOnly) - 1; $i >= 0; $i--) {
            $n = (int) $digitsOnly[$i];
            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alternate = ! $alternate;
        }

        return $sum % 10 === 0;
    }

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
            'booking_items' => ['required'],
            'card_number' => ['required', 'string', 'regex:/^[0-9]{12,19}$/'],
            'expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'cardholder_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{M}\s\-\'.]+$/u'],
            'terms' => ['required', 'accepted'],
        ];
    }
}

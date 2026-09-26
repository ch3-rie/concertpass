<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function getMaskedPaymentMethodAttribute(): string
    {
        $value = trim((string) $this->payment_method);
        $digitsOnly = preg_replace('/\D+/', '', $value) ?? '';

        if ($digitsOnly === '') {
            return $this->payment_method_label;
        }

        $maskedDigits = str_repeat('*', max(0, strlen($digitsOnly) - 4)) . substr($digitsOnly, -4);

        return trim(chunk_split($maskedDigits, 4, ' '));
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $value = trim((string) $this->payment_method);
        $digitsOnly = preg_replace('/\D+/', '', $value) ?? '';

        if ($digitsOnly !== '') {
            return 'Credit Card';
        }

        return str($value)->replace('_', ' ')->title()->value();
    }

    public function getPaymentMethodFormattedAttribute(): string
    {
        $value = trim((string) $this->payment_method);
        $digitsOnly = preg_replace('/\D+/', '', $value) ?? '';

        if ($digitsOnly === '') {
            return $this->payment_method_label;
        }

        return trim(chunk_split($digitsOnly, 4, ' '));
    }
}

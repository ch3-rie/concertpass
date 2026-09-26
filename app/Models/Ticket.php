<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'booking_id',
        'concert_ticket_type_id',
        'seat_id',
        'ticket_type',
        'price_at_purchase',
        'qr_code',
    ];

    protected $casts = [
        'price_at_purchase' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function concertTicketType()
    {
        return $this->belongsTo(ConcertTicketType::class);
    }
}

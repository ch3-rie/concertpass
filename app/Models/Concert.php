<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $fillable = [
        'title',
        'description',
        'artist',
        'venue_id',
        'date',
        'time',
        'poster_url',
        'seat_plan_image',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * True if any booking for this concert has at least one ticket (sold / issued).
     */
    public function hasSoldTickets(): bool
    {
        return $this->bookings()->whereHas('tickets')->exists();
    }

    public function concertTicketTypes()
    {
        return $this->hasMany(ConcertTicketType::class);
    }

    public function ticketPrices()
    {
        return $this->hasMany(ConcertTicketType::class);
    }

    /**
     * Sum of admin-defined quantities across ticket types (total tickets released for the event).
     */
    public function totalTicketAllocation(): int
    {
        if ($this->relationLoaded('concertTicketTypes')) {
            return (int) $this->concertTicketTypes->sum('quantity');
        }
        if ($this->relationLoaded('ticketPrices')) {
            return (int) $this->ticketPrices->sum('quantity');
        }

        return (int) $this->concertTicketTypes()->sum('quantity');
    }

}

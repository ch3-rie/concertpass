<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcertTicketType extends Model
{
    protected $table = 'concert_ticket_options';

    protected $fillable = [
        'concert_id',
        'ticket_type_id',
        'custom_name',
        'price',
        'color',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'sold_quantity',
        'available_quantity',
    ];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function getSectionAttribute()
    {
        return $this->custom_name ?: ($this->ticketType?->name ?? 'Unknown');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'concert_ticket_type_id');
    }

    public function getSoldQuantityAttribute(): int
    {
        // Always derive sold tickets from persisted ticket rows to avoid
        // stale/foreign select aliases overriding inventory values.
        return (int) $this->tickets()->count();
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - $this->sold_quantity);
    }
}

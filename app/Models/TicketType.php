<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function concertTicketTypes()
    {
        return $this->hasMany(ConcertTicketType::class);
    }
}
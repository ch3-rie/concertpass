<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'seat_plan_image',
    ];


    public function concerts()
    {
        return $this->hasMany(Concert::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }


}

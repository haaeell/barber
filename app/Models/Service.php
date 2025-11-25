<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'image',
        'price',
        'duration',
        'description',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

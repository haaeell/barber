<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    protected $fillable = [
        'user_id',
        'nickname',
        'speciality',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function shifts()
    {
        return $this->hasMany(BarberShift::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

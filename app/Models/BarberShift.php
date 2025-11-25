<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarberShift extends Model
{
    protected $fillable = [
        'barber_id',
        'date',
        'start_time',
        'end_time',
        'is_day_off',
    ];

    protected $dates = ['date'];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BarberBookingController extends Controller
{
    public function index()
    {
        $barberId = auth()->user()->barber->id;

        $bookings = Booking::with(['user', 'service'])
            ->where('barber_id', $barberId)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        return view('barber.bookings.index', compact('bookings'));
    }
    public function shifts()
    {
        $barber = auth()->user()->barber;

        $shifts = $barber->shifts()->orderBy('id')->get();

        return view('barber.shifts.index', compact('shifts'));
    }
}

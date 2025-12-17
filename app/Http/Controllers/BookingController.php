<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\BarberShift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function create()
    {
        $day = strtolower(now()->format('l'));

        $barbers = Barber::whereHas('shifts', function ($q) use ($day) {
            $q->where('day_of_week', $day)
                ->where('is_day_off', false);
        })
            ->with('user')
            ->get();
        $services = Service::all();

        return view('booking.create', compact('barbers', 'services'));
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'barber_id'  => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date'
        ]);

        $barberId = $request->barber_id;
        $service  = Service::findOrFail($request->service_id);
        $duration = (int) $service->duration;
        $date     = $request->date;
        $day      = strtolower(Carbon::parse($date)->format('l'));

        if ($duration <= 0) {
            return response()->json([
                'allSlots' => [],
                'booked' => [],
                'available' => [],
                'message' => 'Durasi service tidak valid'
            ]);
        }

        $shift = BarberShift::where('barber_id', $barberId)
            ->whereRaw('LOWER(day_of_week) = ?', [$day])
            ->first();

        if (!$shift || $shift->is_day_off) {
            return response()->json([
                'allSlots' => [],
                'booked' => [],
                'available' => [],
                'message' => 'Barber libur'
            ]);
        }

        $start = Carbon::parse($shift->start_time);
        $end   = Carbon::parse($shift->end_time);

        $allSlots = [];
        $current  = $start->copy();

        while ($current->copy()->addMinutes($duration)->lte($end)) {
            $allSlots[] = $current->format('H:i');
            $current->addMinutes($duration);
        }

        return response()->json([
            'allSlots' => $allSlots,
            'booked' => [],
            'available' => $allSlots
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'barber_id' => 'required',
            'service_id' => 'required',
            'date' => 'required|date',
            'time' => 'required'
        ]);

        $service = Service::find($request->service_id);
        $barber  = Barber::find($request->barber_id);

        $exists = Booking::where('barber_id', $request->barber_id)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Slot sudah diambil orang lain, silakan pilih jam lain.');
        }

        Booking::create([
            'booking_code' => "BOOK-" . strtoupper(uniqid()),
            'user_id'       => auth()->id(),
            'barber_id'     => $request->barber_id,
            'service_id'    => $request->service_id,
            'date'          => $request->date,
            'time'          => $request->time,
            'service_price' => $service->price,
            'barber_price'  => $barber->price,
            'total_price'   => $service->price + $barber->price + 10000,
        ]);

        return back()->with('success', 'Booking berhasil dibuat!');
    }

    public function history()
    {
        $bookings = Booking::with(['barber.user', 'service'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        return view('booking.history', compact('bookings'));
    }
}

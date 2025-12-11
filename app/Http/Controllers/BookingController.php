<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\BarberShift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BookingController extends Controller
{
    public function create()
    {
        $barbers = Barber::with('user')->get();
        $services = Service::all();

        return view('booking.create', compact('barbers', 'services'));
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'barber_id' => 'required',
            'service_id' => 'required',
            'date' => 'required|date'
        ]);

        $barberId = $request->barber_id;
        $service = Service::findOrFail($request->service_id);
        $duration = $service->duration;

        // Ambil shift barber di hari tersebut
        $day = strtolower(Carbon::parse($request->date)->format('l'));

        $shift = BarberShift::where('barber_id', $barberId)
            ->where('day_of_week', $day)
            ->first();

        if (!$shift || $shift->is_day_off) {
            return response()->json([
                'slots' => [],
                'message' => 'Barber libur hari ini'
            ]);
        }

        $start = Carbon::parse($shift->start_time);
        $end   = Carbon::parse($shift->end_time);

        // Generate semua slot
        $allSlots = [];
        $current = $start->copy();

        while ($current->lt($end)) {
            $slotEnd = $current->copy()->addMinutes($duration);

            if ($slotEnd->lte($end)) {
                $allSlots[] = $current->format('H:i');
            }

            $current->addMinutes($duration);
        }

        // Ambil booking existing
        $booked = Booking::where('barber_id', $barberId)
            ->where('date', $request->date)
            ->pluck('time')
            ->toArray();

        // Filter slot yang bentrok
        $available = array_values(array_filter($allSlots, function ($slot) use ($booked) {
            return !in_array($slot, $booked);
        }));

        return response()->json([
            'slots' => $available
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

        Booking::create([
            'booking_code'  => "BOOK-" . time(),
            'user_id'       => auth()->id(),
            'barber_id'     => $request->barber_id,
            'service_id'    => $request->service_id,
            'date'          => $request->date,
            'time'          => $request->time,
            'service_price' => $service->price,
            'barber_price'  => $barber->price,
            'total_price'   => $service->price + $barber->price,
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

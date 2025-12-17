<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\BarberShift;
use App\Models\BookingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{

    private function resolveWeekAndDay(string $date): array
    {
        $carbon = Carbon::parse($date);

        $weekOfMonth = $carbon->weekOfMonth;     // 1–5
        $weekNumber  = (($weekOfMonth - 1) % 4) + 1; // normalize ke 1–4
        $day         = strtolower($carbon->format('l')); // monday, dst

        return [$weekNumber, $day];
    }
    public function create()
    {
        return view('booking.create', [
            'barbers'  => collect(),
            'services' => Service::all(),
        ]);
    }

    /* =============================
        BARBER TERSEDIA BY TANGGAL
    ============================== */
    public function getAvailableBarbers(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        [$weekNumber, $day] = $this->resolveWeekAndDay($request->date);

        $barbers = Barber::whereHas('shifts', function ($q) use ($weekNumber, $day) {
            $q->where('week_number', $weekNumber)
                ->where('day_of_week', $day)
                ->where('is_day_off', false);
        })->with('user')->get();

        return response()->json($barbers);
    }


    /* =============================
        SLOT JAM
    ============================== */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'date'      => 'required|date',
        ]);

        [$weekNumber, $day] = $this->resolveWeekAndDay($request->date);

        $shift = BarberShift::where('barber_id', $request->barber_id)
            ->where('week_number', $weekNumber)
            ->where('day_of_week', $day)
            ->where('is_day_off', false)
            ->first();

        if (!$shift) {
            return response()->json([
                'slots' => [],
                'booked' => [],
            ]);
        }

        /* =========================
           1. SLOT PER 1 JAM
        ========================= */
        $start = Carbon::parse($shift->start_time)->minute(0);
        $end   = Carbon::parse($shift->end_time);

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addHour();
        }

        /* =========================
           2. BOOKED SLOT (FLOOR JAM)
        ========================= */
        $booked = Booking::where('barber_id', $request->barber_id)
            ->where('date', $request->date)
            ->pluck('time')
            ->map(function ($time) {
                return Carbon::parse($time)->minute(0)->format('H:i');
            })
            ->unique()
            ->values();

        return response()->json([
            'allSlots'  => $slots,
            'bookedSlots' => $booked,
        ]);
    }




    /* =============================
        STORE
    ============================== */
    public function store(Request $request)
    {
        $request->validate([
            'barber_id'   => 'required|exists:barbers,id',
            'service_ids' => 'required|array|min:1',
            'date'        => 'required|date',
            'time'        => 'required',
        ]);

        $barber   = Barber::findOrFail($request->barber_id);
        $services = Service::whereIn('id', $request->service_ids)->get();

        $totalDuration     = $services->sum('duration');
        $totalServicePrice = $services->sum('price');

        $startTime = Carbon::parse($request->time);
        $endTime   = $startTime->copy()->addMinutes($totalDuration);

        // cek bentrok
        $existing = Booking::with('services')
            ->where('barber_id', $barber->id)
            ->where('date', $request->date)
            ->get();

        foreach ($existing as $b) {
            $bStart = Carbon::parse($b->time);
            $bEnd   = $bStart->copy()->addMinutes($b->services->sum('duration'));

            if ($startTime < $bEnd && $endTime > $bStart) {
                return back()->with('error', 'Waktu bentrok dengan booking lain');
            }
        }

        $booking = Booking::create([
            'booking_code' => 'BOOK-' . strtoupper(uniqid()),
            'user_id'      => auth()->id(),
            'barber_id'    => $barber->id,
            'date'         => $request->date,
            'time'         => $startTime,
            'barber_price' => $barber->price,
            'service_price' => $totalServicePrice,
            'total_price'  => $totalServicePrice + $barber->price,
            'status'       => 'pending',
        ]);

        foreach ($services as $service) {
            BookingService::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'price'      => $service->price,
                'duration'   => $service->duration,
            ]);
        }

        return redirect()->route('booking.history')
            ->with('success', 'Booking berhasil dibuat');
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

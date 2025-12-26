<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $status  = $request->status;
        $barber  = $request->barber;
        $service = $request->service;
        $search  = $request->search;
        $from    = $request->from;
        $to      = $request->to;

        $today = Carbon::today()->format('Y-m-d');

        $bookings = Booking::with(['barber.user', 'services.service', 'user'])

            // ================= ROLE CHECK =================
            ->when($user->role === 'barber', function ($q) use ($user) {
                if ($user->barber) {
                    $q->where('barber_id', $user->barber->id);
                } else {
                    $q->whereRaw('1=0');
                }
            })

            // ================= FILTER =================
            ->when($status, fn($q) => $q->where('status', $status))

            ->when(
                $user->role !== 'barber' && $barber,
                fn($q) => $q->where('barber_id', $barber)
            )

            ->when($service, fn($q) => $q->whereHas(
                'services',
                fn($s) =>
                $s->where('service_id', $service)
            ))

            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                    ->orWhereHas('barber.user', fn($b) => $b->where('name', 'like', "%$search%"));
            })

            // ================= FILTER TANGGAL =================
            ->when(
                $from && $to,
                fn($q) => $q->whereBetween('date', [$from, $to])
            )

            // ================= DEFAULT WALK-IN HARI INI =================
            ->when(
                !($from && $to),
                fn($q) => $q->where(function ($w) use ($today) {
                    $w->where('source', 'online')
                        ->orWhere(function ($wi) use ($today) {
                            $wi->where('source', 'walk_in')
                                ->whereDate('date', $today);
                        });
                })
            )

            ->orderByRaw("
                CASE 
                    WHEN status IN ('completed', 'canceled') THEN 1
                    ELSE 0
                END
            ")
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        // ================= DATA FILTER =================
        $barbers = $user->role === 'barber'
            ? Barber::where('id', optional($user->barber)->id)->with('user')->get()
            : Barber::with('user')->get();

        $services = Service::all();

        return view('admin.bookings.index', compact(
            'bookings',
            'status',
            'barbers',
            'services'
        ));
    }

    public function updateStatus(Request $request)
    {
        $booking = Booking::findOrFail($request->id);
        $booking->status = $request->status;
        $booking->save();

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }

    public function complete(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'payment_method' => 'required|in:cash,qris,transfer',
        ]);

        $booking = Booking::findOrFail($request->id);
        $booking->status = 'completed';
        $booking->payment_method = $request->payment_method;
        $booking->payment_status = 'paid';
        $booking->save();

        return back()->with('success', 'Booking berhasil diselesaikan.');
    }

    public function walkIn(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($request->service_id);
        $barber = Barber::findOrFail($request->barber_id);

        $isHaircut = strtolower($service->name) === 'haircut';

        $servicePrice = $isHaircut
            ? $barber->price       // haircut ikut barber
            : $service->price;     // selain haircut

        $barberPrice = $isHaircut
            ? $barber->price       // cuma info
            : 0;

        $totalPrice = $servicePrice; // barber tidak nambah kalau non-haircut

        $booking = Booking::create([
            'booking_code' => 'WI-' . now()->format('YmdHis'),
            'user_id' => null,
            'customer_name' => $request->customer_name,
            'source' => 'walk_in',

            'barber_id' => $barber->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),

            'service_price' => $servicePrice,
            'barber_price' => $barberPrice,
            'total_price' => $totalPrice,

            'status' => 'checkin',
            'payment_status' => 'unpaid',
        ]);

        $booking->services()->create([
            'service_id' => $service->id,
            'price' => $servicePrice,
            'duration' => $service->duration,
        ]);

        return back()->with('success', 'Order walk-in berhasil dibuat (CHECK-IN).');
    }


    public function updateServices(Request $request)
    {
        $request->validate([
            'booking_id'    => 'required|exists:bookings,id',
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ]);

        $booking = Booking::with(['services.service', 'barber'])
            ->findOrFail($request->booking_id);

        if ($booking->status === 'completed') {
            return back()->with('error', 'Booking sudah selesai, tidak bisa diubah.');
        }

        $booking->services()->delete();

        $totalServicePrice = 0;
        $totalDuration     = 0;
        $hasHaircut        = false;

        foreach ($request->service_ids as $serviceId) {
            $service = Service::findOrFail($serviceId);

            if (strtolower($service->name) === 'haircut') {
                $hasHaircut = true;
            }

            $booking->services()->create([
                'service_id' => $service->id,
                'price'      => $service->price,
                'duration'   => $service->duration,
            ]);

            $totalServicePrice += $service->price;
            $totalDuration     += $service->duration;
        }

        $barberPrice = $hasHaircut ? $booking->barber->price : 0;

        $totalPrice = $totalServicePrice + $barberPrice;

        $adminFee = $booking->source === 'online' ? 5000 : 0;
        $totalPrice += $adminFee;

        $booking->update([
            'service_price' => $totalServicePrice,
            'barber_price'  => $barberPrice,
            'total_price'   => $totalPrice,
        ]);

        return back()->with('success', 'Service booking berhasil diperbarui dan harga disesuaikan.');
    }


    public function changeBarber(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'barber_id'  => 'required|exists:barbers,id',
        ]);

        $booking = Booking::with(['services.service'])->findOrFail($request->booking_id);

        if (in_array($booking->status, ['completed', 'canceled'])) {
            return back()->with('error', 'Booking tidak bisa diubah');
        }

        $newBarber = Barber::findOrFail($request->barber_id);

        $hasHaircut = $booking->services->contains(function ($svc) {
            return strtolower($svc->service->name) === 'haircut';
        });

        $servicePrice = $booking->service_price;
        $barberPrice  = $hasHaircut ? $newBarber->price : 0;

        $booking->update([
            'barber_id'    => $newBarber->id,
            'barber_price' => $barberPrice,
            'total_price'  => $servicePrice + $barberPrice,
        ]);

        return back()->with('success', 'Kapster berhasil diganti dan harga diperbarui');
    }
}

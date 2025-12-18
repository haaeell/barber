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

        $bookings = Booking::with(['barber.user', 'service', 'user'])

            // ================= ROLE CHECK =================
            ->when($user->role === 'barber', function ($q) use ($user) {
                if ($user->barber) {
                    $q->where('barber_id', $user->barber->id);
                } else {
                    // safety: admin tanpa barber → kosong
                    $q->whereRaw('1=0');
                }
            })

            // ================= FILTER =================
            ->when($status, fn($q) => $q->where('status', $status))

            ->when(
                $user->role !== 'barber' && $barber,
                fn($q) => $q->where('barber_id', $barber)
            )

            ->when($service, fn($q) => $q->where('service_id', $service))

            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                    ->orWhereHas('barber.user', fn($b) => $b->where('name', 'like', "%$search%"))
                    ->orWhereHas('service', fn($s) => $s->where('name', 'like', "%$search%"));
            })

            ->when($from && $to, fn($q) => $q->whereBetween('date', [$from, $to]))

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
            'barber_id'     => 'required|exists:barbers,id',
            'service_id'    => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($request->service_id);
        $barber  = Barber::findOrFail($request->barber_id);

        $booking = Booking::create([
            'booking_code'  => 'WI-' . now()->format('YmdHis'),
            'user_id'       => null,
            'customer_name' => $request->customer_name,
            'source'        => 'walk_in',

            'barber_id'     => $barber->id,
            'date'          => Carbon::parse($request->date)->format('Y-m-d'),
            'time'          => Carbon::parse($request->time)->format('H:i:s'),

            'service_price' => $service->price,
            'barber_price'  => $barber->price,
            'total_price'   => $barber->price + $service->price,

            'status'        => 'checkin',
            'payment_status' => 'unpaid',
        ]);

        $booking->services()->create([
            'service_id' => $service->id,
            'price'      => $service->price,
            'duration'   => $service->duration,
        ]);

        return back()->with('success', 'Order walk-in berhasil dibuat (CHECK-IN).');
    }

    public function updateServices(Request $request)
    {
        $request->validate([
            'booking_id'  => 'required|exists:bookings,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ]);

        $booking = Booking::with('services')->findOrFail($request->booking_id);

        if ($booking->status === 'completed') {
            return back()->with('error', 'Booking sudah selesai, tidak bisa diubah.');
        }

        // hapus service lama
        $booking->services()->delete();

        $totalServicePrice = 0;
        $totalDuration     = 0;

        foreach ($request->service_ids as $serviceId) {
            $service = Service::findOrFail($serviceId);

            $booking->services()->create([
                'service_id' => $service->id,
                'price'      => $service->price,
                'duration'   => $service->duration,
            ]);

            $totalServicePrice += $service->price;
            $totalDuration     += $service->duration;
        }

        // update total
        $booking->update([
            'service_price' => $totalServicePrice,
            'total_price'   => $totalServicePrice + $booking->barber_price,
        ]);

        return back()->with('success', 'Service booking berhasil diperbarui.');
    }
}

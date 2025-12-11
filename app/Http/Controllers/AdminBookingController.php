<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Service;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status;
        $barber = $request->barber;
        $service = $request->service;
        $search = $request->search;
        $from = $request->from;
        $to = $request->to;

        $bookings = Booking::with(['barber.user', 'service', 'user'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($barber, fn($q) => $q->where('barber_id', $barber))
            ->when($service, fn($q) => $q->where('service_id', $service))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                    ->orWhereHas('barber.user', fn($b) => $b->where('name', 'like', "%$search%"))
                    ->orWhereHas('service', fn($s) => $s->where('name', 'like', "%$search%"));
            })
            ->when($from && $to, fn($q) => $q->whereBetween('date', [$from, $to]))
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        $barbers = Barber::with('user')->get();
        $services = Service::all();

        return view('admin.bookings.index', compact('bookings', 'status', 'barbers', 'services'));
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
}

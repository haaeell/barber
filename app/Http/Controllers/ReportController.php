<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // FILTER
        $from = $request->from ?? Carbon::today()->toDateString();
        $to = $request->to ?? Carbon::today()->toDateString();

        // ALL BOOKINGS WITHIN RANGE
        $bookings = Booking::with(['barber.user', 'service', 'user'])
            ->whereBetween('date', [$from, $to])
            ->where('payment_status', 'paid')
            ->orderBy('date', 'asc')
            ->get();

        // TOTAL INCOME
        $totalIncome = $bookings->sum('total_price');

        // PAYMENT BREAKDOWN
        $paymentBreakdown = [
            'cash' => $bookings->where('payment_method', 'cash')->sum('total_price'),
            'qris' => $bookings->where('payment_method', 'qris')->sum('total_price'),
        ];

        // PER BARBER
        $incomePerBarber = Barber::with(['user', 'bookings'])
            ->get()
            ->map(function ($barber) use ($from, $to) {
                $income = $barber->bookings
                    ->where('payment_status', 'paid')
                    ->whereBetween('date', [$from, $to])
                    ->sum('total_price');

                return [
                    'name' => $barber->user->name,
                    'income' => $income
                ];
            });

        // PER SERVICE
        $incomePerService = Service::with('bookings')
            ->get()
            ->map(function ($service) use ($from, $to) {
                $income = $service->bookings
                    ->where('payment_status', 'paid')
                    ->whereBetween('date', [$from, $to])
                    ->sum('total_price');

                return [
                    'name' => $service->name,
                    'income' => $income
                ];
            });

        return view('reports.index', compact(
            'from',
            'to',
            'bookings',
            'totalIncome',
            'paymentBreakdown',
            'incomePerBarber',
            'incomePerService'
        ));
    }

    public function barberReport(Request $request, $id)
    {
        $from = $request->from ?? now()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $barber = \App\Models\Barber::with('user')->findOrFail($id);

        // Semua transaksi barber
        $bookings = \App\Models\Booking::with(['service', 'user'])
            ->where('barber_id', $id)
            ->whereBetween('date', [$from, $to])
            ->where('payment_status', 'paid')
            ->orderBy('date', 'asc')
            ->get();

        // Basic summary
        $totalIncome = $bookings->sum('total_price');
        $totalBooking = $bookings->count();
        $average = $totalBooking > 0 ? $totalIncome / $totalBooking : 0;

        // Service paling sering dipesan
        $topServices = $bookings->groupBy('service_id')->map(function ($row) {
            return [
                'service' => $row->first()->service->name,
                'count' => $row->count(),
                'income' => $row->sum('total_price')
            ];
        })->sortByDesc('count')->values();

        return view('reports.barber', compact(
            'barber',
            'from',
            'to',
            'totalIncome',
            'totalBooking',
            'average',
            'bookings',
            'topServices'
        ));
    }
}

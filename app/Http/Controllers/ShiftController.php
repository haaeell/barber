<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\BarberShift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $barbers = Barber::with([
            'user',
            'shifts' => function ($q) {
                $q->orderBy('week_number');
            }
        ])->get();

        $weeks = [1, 2, 3, 4];

        return view('shifts.index', compact('barbers', 'days', 'weeks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'day_of_week' => 'required',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_day_off' => 'required|boolean',
        ]);

        BarberShift::updateOrCreate(
            [
                'barber_id'   => $request->barber_id,
                'day_of_week' => $request->day_of_week,
                'week_number' => $request->week_number,
            ],
            [
                'start_time'  => $request->is_day_off ? null : $request->start_time,
                'end_time'    => $request->is_day_off ? null : $request->end_time,
                'is_day_off'  => $request->is_day_off,
            ]
        );


        return back()->with('success', 'Shift berhasil disimpan.');
    }

    public function rolling()
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $barbers = Barber::pluck('id')->values();
        $totalBarbers = $barbers->count();

        if ($totalBarbers < 2) {
            return back()->with('success', 'Minimal harus ada 2 barber.');
        }

        // reset jadwal lama
        BarberShift::truncate();

        $startTime = '09:00';
        $endTime   = '17:00';

        /**
         * Generate 4 minggu
         */
        for ($week = 1; $week <= 4; $week++) {

            /**
             * Geser urutan barber tiap minggu
             * supaya hari libur tidak selalu jatuh ke orang yang sama
             */
            $rotatedBarbers = $barbers->slice($week - 1)
                ->merge($barbers->slice(0, $week - 1))
                ->values();

            foreach ($days as $dayIndex => $day) {

                /**
                 * Tentukan barber libur hari ini
                 */
                $offBarberId = $rotatedBarbers[$dayIndex % $totalBarbers];

                foreach ($barbers as $barberId) {

                    $isWorking = $barberId !== $offBarberId;

                    BarberShift::create([
                        'barber_id'   => $barberId,
                        'week_number' => $week,
                        'day_of_week' => $day,
                        'start_time'  => $isWorking ? $startTime : null,
                        'end_time'    => $isWorking ? $endTime : null,
                        'is_day_off'  => !$isWorking,
                    ]);
                }
            }
        }

        return back()->with('success', 'Jadwal rolling 4 minggu berhasil dibuat.');
    }
}

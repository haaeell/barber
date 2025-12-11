<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\BarberShift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $barbers = Barber::with('user')->get();
        $shifts = BarberShift::with('barber.user')->orderBy('barber_id')->get();

        return view('shifts.index', compact('barbers', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'day_of_week' => 'required',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_day_off' => 'boolean',
        ]);

        BarberShift::create($request->all());

        return back()->with('success', 'Shift berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'day_of_week' => 'required',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_day_off' => 'boolean',
        ]);

        BarberShift::findOrFail($id)->update($request->all());

        return back()->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy($id)
    {
        BarberShift::findOrFail($id)->delete();

        return back()->with('success', 'Shift berhasil dihapus.');
    }
}

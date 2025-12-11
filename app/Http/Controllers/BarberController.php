<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    public function index()
    {
        $barbers = Barber::with('user')->orderBy('id', 'DESC')->get();
        $users = User::where('role', 'barber')->get(); // FIX: ambil user barber

        return view('barbers.index', compact('barbers', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nickname' => 'nullable|string',
            'speciality' => 'nullable|string',
            'is_active' => 'boolean',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('barbers', 'public');
        }

        Barber::create($data);

        return redirect()->route('barbers.index')
            ->with('success', 'Barber berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nickname' => 'nullable|string',
            'speciality' => 'nullable|string',
            'is_active' => 'boolean',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048'
        ]);

        $barber = Barber::findOrFail($id);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('barbers', 'public');
        }

        $barber->update($data);

        return redirect()->route('barbers.index')
            ->with('success', 'Barber berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Barber::findOrFail($id)->delete();

        return back()->with('success', 'Barber berhasil dihapus.');
    }
}

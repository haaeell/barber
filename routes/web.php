<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\BarberBookingController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('barbers', BarberController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('shifts', ShiftController::class);
    Route::resource('users', UserController::class);

    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/slots', [BookingController::class, 'getAvailableSlots']);
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/history', [BookingController::class, 'history'])->name('booking.history');

    Route::get('/admin/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
    Route::post('/admin/bookings/update-status', [AdminBookingController::class, 'updateStatus'])->name('admin.bookings.updateStatus');
    Route::post('/admin/bookings/complete', [AdminBookingController::class, 'complete'])
        ->name('admin.bookings.complete');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/barber/{id}', [ReportController::class, 'barberReport'])
        ->name('reports.barber');

    Route::prefix('barber')->group(function () {

        Route::get('/bookings', [BarberBookingController::class, 'index'])
            ->name('barber.bookings');
        Route::get('/shifts', [BarberBookingController::class, 'shifts'])
            ->name('barber.shifts');
    });

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

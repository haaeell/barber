@php
    function statusBadge($status)
    {
        switch ($status) {
            case 'pending':
                return 'badge badge-warning';
            case 'confirmed':
                return 'badge badge-info';
            case 'checkin':
                return 'badge badge-primary';
            case 'completed':
                return 'badge badge-success';
            case 'canceled':
                return 'badge badge-danger';
        }
    }
@endphp

@extends('layouts.app')

@section('content')
    <style>
        .booking-card {
            border-radius: 12px;
            border: 1px solid #eee;
            padding: 18px;
            display: flex;
            margin-bottom: 15px;
            transition: 0.2s;
            background: white;
        }

        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .booking-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }

        .action-btn {
            font-size: 14px;
            padding: 6px 12px;
        }
    </style>

    <div class="container mt-4">

        <h2 class="fw-bold mb-4">Riwayat Booking</h2>

        @foreach ($bookings as $booking)
            <div class="booking-card shadow-sm">

                {{-- FOTO BARBER --}}
                <img src="{{ asset('storage/' . ($booking->barber->image ?? 'default-barber.jpg')) }}" class="booking-image">

                {{-- INFORMASI BOOKING --}}
                <div style="flex:1">
                    <h5 class="mb-1">
                        {{ $booking->service->name }}
                    </h5>

                    <p class="text-muted mb-1">
                        Barber: <b>{{ $booking->barber->user->name }}</b>
                    </p>

                    <p class="mb-1">
                        Tanggal: <b>{{ $booking->date }}</b><br>
                        Jam: <b>{{ $booking->time }}</b>
                    </p>

                    <p class="mb-1">
                        Total: <b>Rp {{ number_format($booking->total_price) }}</b>
                    </p>

                    <span class="{{ statusBadge($booking->status) }}">
                        {{ ucfirst($booking->status) }}
                    </span>

                    {{-- REVIEW BUTTON --}}
                    @if ($booking->status == 'completed' && !$booking->review)
                        <a href="#" class="btn btn-sm btn-primary action-btn mt-2">
                            Beri Review
                        </a>
                    @endif

                </div>

                {{-- FOTO SERVICE --}}
                <img src="{{ asset('storage/' . ($booking->service->image ?? 'default-service.jpg')) }}"
                    class="booking-image">

            </div>
        @endforeach

        @if ($bookings->count() == 0)
            <div class="alert alert-info">Belum ada booking.</div>
        @endif

    </div>
@endsection

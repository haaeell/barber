@php
    function statusBadge($status)
    {
        return match ($status) {
            'pending' => 'badge badge-warning',
            'confirmed' => 'badge badge-info',
            'checkin' => 'badge badge-primary',
            'completed' => 'badge badge-success',
            'canceled' => 'badge badge-danger',
            default => 'badge badge-secondary',
        };
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
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }

        .service-badge {
            background: #f1f5f9;
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 13px;
            margin: 3px 4px 3px 0;
            display: inline-block;
        }

        .action-btn {
            font-size: 14px;
            padding: 6px 12px;
        }
    </style>

    <div class="container mt-4">

        <h2 class="fw-bold mb-4">Riwayat Booking</h2>

        @forelse ($bookings as $booking)
            <div class="booking-card shadow-sm">

                {{-- FOTO BARBER --}}
                <img src="{{ asset('storage/' . ($booking->barber->image ?? 'default-barber.jpg')) }}" class="booking-image">

                {{-- INFO --}}
                <div style="flex:1">

                    {{-- SERVICES --}}
                    <h5 class="mb-2">
                        @foreach ($booking->services as $svc)
                            <span class="service-badge">
                                {{ $svc->service->name }}
                            </span>
                        @endforeach
                    </h5>

                    <p class="text-muted mb-1">
                        Barber: <b>{{ $booking->barber->user->name }}</b>
                    </p>

                    <p class="mb-1">
                        Tanggal: <b>{{ $booking->date->format('d M Y') }}</b><br>
                        Jam: <b>{{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</b>
                        <span class="text-muted">
                            ({{ $booking->total_service_duration }} menit)
                        </span>
                    </p>

                    <p class="mb-2">
                        Total:
                        <b class="text-primary">
                            Rp {{ number_format($booking->total_price) }}
                        </b>
                    </p>

                    <span class="{{ statusBadge($booking->status) }}">
                        {{ ucfirst($booking->status) }}
                    </span>

                    {{-- REVIEW --}}
                    @if ($booking->status === 'completed' && !$booking->review)
                        <div class="mt-2">
                            <a href="#" class="btn btn-sm btn-primary action-btn">
                                Beri Review
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div class="alert alert-info">Belum ada booking.</div>
        @endforelse

    </div>
@endsection

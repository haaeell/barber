@php
    function statusBadge($status)
    {
        return [
            'pending' => 'badge badge-warning',
            'confirmed' => 'badge badge-info',
            'checkin' => 'badge badge-primary',
            'completed' => 'badge badge-success',
            'canceled' => 'badge badge-danger',
        ][$status] ?? 'badge badge-secondary';
    }
@endphp
@extends('layouts.app')

@section('content')
    <style>
        .booking-admin-card {
            border-radius: 12px;
            border: 1px solid #eee;
            padding: 18px;
            margin-bottom: 12px;
            background: #fff;
            transition: 0.2s;
            display: flex;
            align-items: center;
        }

        .booking-admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .booking-img {
            width: 90px;
            height: 90px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
        }

        .action-btn {
            margin-right: 6px;
            font-size: 13px;
            padding: 6px 10px;
        }
    </style>

    <div class="">

        <h2 class="fw-bold mb-4">Booking Masuk</h2>

        {{-- FILTER BAR --}}
        <div class="card p-3 shadow-sm mb-4">
            <form method="GET" class="row">

                {{-- STATUS --}}
                <div class="col-md-2 mb-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed
                        </option>
                        <option value="checkin" {{ request('status') == 'checkin' ? 'selected' : '' }}>Check-in</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                {{-- BARBER --}}
                <div class="col-md-2 mb-2">
                    <label>Barber</label>
                    <select name="barber" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($barbers as $b)
                            <option value="{{ $b->id }}" {{ request('barber') == $b->id ? 'selected' : '' }}>
                                {{ $b->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SERVICE --}}
                <div class="col-md-2 mb-2">
                    <label>Service</label>
                    <select name="service" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($services as $s)
                            <option value="{{ $s->id }}" {{ request('service') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DATE RANGE --}}
                <div class="col-md-2 mb-2">
                    <label>Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-2 mb-2">
                    <label>Sampai</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                {{-- SEARCH --}}
                <div class="col-md-2 mb-2">
                    <label>Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama customer/barber/service"
                        value="{{ request('search') }}">
                </div>

                {{-- SUBMIT --}}
                <div class="col-md-12 mt-3">
                    <button class="btn btn-primary">
                        <i class="fe fe-search"></i> Filter
                    </button>
                    <a href="/admin/bookings" class="btn btn-secondary">
                        Reset
                    </a>
                </div>

            </form>
        </div>


        {{-- FILTER STATUS --}}
        <div class="mb-3">
            <form method="GET" class="form-inline">
                <select name="status" class="form-control mr-2">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checkin" {{ $status == 'checkin' ? 'selected' : '' }}>Check-in</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="canceled" {{ $status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
                <button class="btn btn-primary">Filter</button>
            </form>
        </div>

        @foreach ($bookings as $booking)
            <div class="booking-admin-card shadow-sm">

                {{-- FOTO BARBER --}}
                <img src="{{ asset('storage/' . ($booking->barber->image ?? 'default-barber.jpg')) }}" class="booking-img">

                {{-- DETAIL BOOKING --}}
                <div style="flex:1">
                    <h5 class="mb-1">
                        {{ $booking->service->name }}
                        <span class="{{ statusBadge($booking->status) }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </h5>

                    <p class="text-muted mb-1">
                        Customer: <b>{{ $booking->user->name }}</b>
                    </p>

                    <p class="mb-1">
                        Barber: <b>{{ $booking->barber->user->name }}</b>
                    </p>

                    <p class="mb-1">
                        Tanggal: <b>{{ $booking->date }}</b> | Jam: <b>{{ $booking->time }}</b>
                    </p>

                    <p class="mb-1">
                        Total Harga: <b>Rp {{ number_format($booking->total_price) }}</b>
                    </p>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="text-right">

                    <form method="POST" action="{{ route('admin.bookings.updateStatus') }}">
                        @csrf

                        <input type="hidden" name="id" value="{{ $booking->id }}">

                        @if ($booking->status == 'pending')
                            <button name="status" value="confirmed" class="btn btn-info action-btn">
                                Confirm
                            </button>
                        @endif

                        @if ($booking->status == 'confirmed')
                            <button name="status" value="checkin" class="btn btn-primary action-btn">
                                Check-in
                            </button>
                        @endif

                        @if ($booking->status == 'checkin')
                            <button type="button" onclick="openPaymentModal({{ $booking->id }})"
                                class="btn btn-success action-btn">
                                Complete
                            </button>
                        @endif

                        @if (!in_array($booking->status, ['completed', 'canceled']))
                            <button name="status" value="canceled" class="btn btn-danger action-btn">
                                Cancel
                            </button>
                        @endif

                    </form>

                </div>

            </div>
        @endforeach

        @if ($bookings->count() == 0)
            <div class="alert alert-info">Belum ada booking.</div>
        @endif


        {{-- MODAL PILIH PEMBAYARAN --}}
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <form method="POST" action="{{ route('admin.bookings.complete') }}">
                        @csrf

                        <input type="hidden" id="payment_booking_id" name="id">

                        <div class="modal-header">
                            <h5 class="modal-title">Pilih Metode Pembayaran</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">

                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="cash">Cash</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-success w-100">Selesaikan Pembayaran</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <script>
            function openPaymentModal(id) {
                document.getElementById('payment_booking_id').value = id;
                $('#paymentModal').modal('show');
            }
        </script>


    </div>
@endsection

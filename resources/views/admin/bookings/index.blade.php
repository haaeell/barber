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
    <div class="">


        {{-- FILTER BAR --}}
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="fw-bold mb-0">Data Booking & Order</h3>

                <div class="card-toolbar">
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalWalkIn">
                        <i class="fe fe-plus"></i> Order Manual
                    </button>
                </div>
            </div>
        </div>
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
                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled
                        </option>
                    </select>
                </div>

                @if (auth()->user()->role !== 'admin')
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
                @endif


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

        {{-- ALERT --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- TABLE --}}
        <div class="card shadow-sm">
            <div class="card-body table-responsive">

                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Customer</th>
                            <th>Barber</th>
                            <th>Service</th>
                            <th>Sumber</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th width="220">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($bookings as $i => $b)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $b->date }}</td>
                                <td>{{ $b->time }}</td>
                                <td>{{ $b->customer_label }}
                                </td>
                                <td>{{ $b->barber->user->name }}</td>
                                <td>{{ $b->service->name }}</td>

                                <td>
                                    <span class="badge {{ $b->source == 'walk_in' ? 'badge-dark' : 'badge-info' }}">
                                        {{ strtoupper($b->source) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="{{ statusBadge($b->status) }}">
                                        {{ ucfirst($b->status) }}
                                    </span>
                                </td>

                                <td>
                                    Rp {{ number_format($b->total_price) }}
                                </td>

                                <td>
                                    <form method="POST" action="{{ route('admin.bookings.updateStatus') }}"
                                        class="d-inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $b->id }}">

                                        @if ($b->status == 'pending')
                                            <button name="status" value="confirmed"
                                                class="btn btn-info btn-sm">Confirm</button>
                                        @endif

                                        @if ($b->status == 'confirmed')
                                            <button name="status" value="checkin"
                                                class="btn btn-primary btn-sm">Check-in</button>
                                        @endif

                                        @if ($b->status == 'checkin')
                                            <button type="button" onclick="openPaymentModal({{ $b->id }})"
                                                class="btn btn-success btn-sm">Bayar</button>
                                        @endif

                                        @if (!in_array($b->status, ['completed', 'canceled']))
                                            <button name="status" value="canceled"
                                                class="btn btn-danger btn-sm">Cancel</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

        <div class="modal fade" id="modalWalkIn" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.bookings.walkin') }}">
                    @csrf

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Order Walk-in (Langsung Bayar)</h5>
                            <button class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>

                        <div class="modal-body">

                            <label>Nama Customer</label>
                            <input type="text" name="customer_name" class="form-control mb-3" required>

                            <label>Barber</label>
                            <select name="barber_id" class="form-control mb-3" required>
                                @foreach ($barbers as $b)
                                    <option value="{{ $b->id }}">{{ $b->user->name }}</option>
                                @endforeach
                            </select>

                            <label>Service</label>
                            <select name="service_id" class="form-control mb-3" required>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>

                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control mb-3"
                                value="{{ now()->format('Y-m-d') }}" required>

                            <label>Jam</label>
                            <input type="time" name="time" class="form-control mb-3"
                                value="{{ now()->format('H:i') }}" required>

                            <label>Metode Pembayaran</label>
                            <select name="payment_method" class="form-control mb-3" required>
                                <option value="">-- Pilih --</option>
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                            </select>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-success w-100">
                                <i class="fe fe-check-circle"></i> Simpan & Selesaikan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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

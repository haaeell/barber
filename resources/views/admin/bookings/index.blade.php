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
    <div class="container-fluid">

        {{-- ================= HEADER ================= --}}
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0">Data Booking & Order</h3>

                <button class="btn btn-success" data-toggle="modal" data-target="#modalWalkIn">
                    <i class="fe fe-plus"></i> Order Manual
                </button>
            </div>
        </div>

        {{-- ================= FILTER ================= --}}
        <div class="card p-3 shadow-sm mb-4">
            <form method="GET" class="row">
                <div class="col-md-2 mb-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        @foreach (['pending', 'confirmed', 'checkin', 'completed', 'canceled'] as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                {{ ucfirst($st) }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

                <div class="col-md-2 mb-2">
                    <label>Dari</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-2 mb-2">
                    <label>Sampai</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-2 mb-2">
                    <label>Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Customer / Barber"
                        value="{{ request('search') }}">
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        <i class="fe fe-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- ================= TABS ================= --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button class="nav-link active" id="tab-online-btn" data-toggle="tab" data-target="#tab-online">
                    📱 Booking Online
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-walkin-btn" data-toggle="tab" data-target="#tab-walkin">
                    🚶 Order Manual
                </button>
            </li>
        </ul>


        <div class="tab-content">

            {{-- ================= TAB ONLINE ================= --}}
            <div class="tab-pane fade show active" id="tab-online">
                @include('admin.bookings._table', [
                    'rows' => $bookings->where('source', 'online'),
                ])
            </div>

            {{-- ================= TAB WALK IN ================= --}}
            <div class="tab-pane fade" id="tab-walkin">
                @include('admin.bookings._table', [
                    'rows' => $bookings->where('source', 'walk_in'),
                ])
            </div>

        </div>
    </div>

    {{-- ================= MODAL WALK IN ================= --}}
    <div class="modal fade" id="modalWalkIn" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.bookings.walkin') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Order Walk-in</h5>
                        <button class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <label>Nama Customer</label>
                        <input type="text" name="customer_name" class="form-control mb-2" required>

                        <label>Barber</label>
                        <select name="barber_id" class="form-control mb-2" required>
                            @foreach ($barbers as $b)
                                <option value="{{ $b->id }}">{{ $b->user->name }}</option>
                            @endforeach
                        </select>

                        <label>Service</label>
                        <select name="service_id" class="form-control mb-2" required>
                            @foreach ($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success w-100">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL PAYMENT ================= --}}
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.bookings.complete') }}" class="modal-content">
                @csrf
                <input type="hidden" name="id" id="payment_booking_id">

                <div class="modal-header">
                    <h5>Pilih Metode Pembayaran</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <select name="payment_method" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success w-100">Selesaikan</button>
                </div>
            </form>
        </div>
    </div>



    <div class="modal fade" id="modalEditService" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.bookings.updateServices') }}" class="modal-content">
                @csrf

                <input type="hidden" name="booking_id" id="edit_booking_id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Service Booking</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <label>Service</label>

                    @foreach ($services as $s)
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox" name="service_ids[]"
                                value="{{ $s->id }}" id="svc{{ $s->id }}">

                            <label class="form-check-label" for="svc{{ $s->id }}">
                                {{ $s->name }} — Rp {{ number_format($s->price) }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary w-100">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const TAB_KEY = 'booking_active_tab';

        document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => {
            tab.addEventListener('click', function() {
                localStorage.setItem(TAB_KEY, this.id);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem(TAB_KEY);

            if (activeTab) {
                const tabButton = document.getElementById(activeTab);
                if (tabButton) {
                    $(tabButton).tab('show');
                }
            }
        });

        document.querySelector('#modalWalkIn form')?.addEventListener('submit', function() {
            localStorage.setItem(TAB_KEY, 'tab-walkin-btn');
        });

        function openPaymentModal(id) {
            document.getElementById('payment_booking_id').value = id
            $('#paymentModal').modal('show')
        }

        function openServiceModal(bookingId, selectedServices) {
            document.getElementById('edit_booking_id').value = bookingId;

            document.querySelectorAll('.service-checkbox').forEach(cb => {
                cb.checked = selectedServices.includes(parseInt(cb.value));
            });

            $('#modalEditService').modal('show');
        }
    </script>
@endsection

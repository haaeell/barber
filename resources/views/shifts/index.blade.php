@extends('layouts.app')

@section('content')
    <style>
        /* ====== GLOBAL ====== */
        .page-subtitle {
            font-size: 13px;
            color: #6c757d;
        }

        /* ====== CARD ====== */
        .week-card {
            border-radius: 14px;
        }

        .week-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f3f5;
        }

        /* ====== TABLE ====== */
        .shift-table th {
            font-size: 11px;
            letter-spacing: .6px;
            color: #6c757d;
            text-transform: uppercase;
            border-bottom: 1px solid #f1f3f5;
            padding: 12px;
        }

        .shift-table td {
            padding: 14px 10px;
            border-color: #f1f3f5;
        }

        .shift-table tbody tr:hover {
            background-color: #fafbfc;
        }

        /* ====== BADGES ====== */
        .badge-shift {
            background: #e9f7ef;
            color: #198754;
            font-weight: 500;
            font-size: 12px;
            padding: 6px 12px;
        }

        .badge-off {
            color: #adb5bd;
            font-weight: 500;
            font-size: 12px;
            letter-spacing: .3px;
        }

        /* ====== AVATAR ====== */
        .avatar-soft {
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
        }

        /* ====== CELL ====== */
        .shift-cell:hover {
            background: #f8f9fa;
        }
    </style>

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Jadwal Shift Barber</h3>
                <div class="page-subtitle">Pengaturan shift mingguan (4 minggu)</div>
            </div>

            <form action="{{ route('shifts.rolling') }}" method="POST"
                onsubmit="return confirm('Generate jadwal otomatis 4 minggu?')">
                @csrf
                <button class="btn btn-warning fw-semibold">
                    <i class="fe fe-refresh-cw"></i> Rolling 4 Minggu
                </button>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @php
            $weeks = [1, 2, 3, 4];
            function inisial($nama)
            {
                return collect(explode(' ', $nama))->map(fn($n) => strtoupper(substr($n, 0, 1)))->join('');
            }
        @endphp

        {{-- ================= WEEK CARDS ================= --}}
        @foreach ($weeks as $week)
            <div class="card week-card shadow-sm border-0 mb-4">

                {{-- HEADER CARD --}}
                <div class="week-header d-flex align-items-center bg-white">
                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 me-3">
                        Minggu {{ $week }}
                    </span>
                    <span class="text-muted small">Klik sel untuk mengatur shift</span>
                </div>

                {{-- BODY --}}
                <div class="card-body table-responsive p-0">
                    <table class="table shift-table align-middle text-center mb-0">

                        <thead>
                            <tr>
                                <th class="text-start ps-4">Barber</th>
                                @foreach ($days as $day)
                                    <th>{{ ucfirst(substr($day, 0, 3)) }}</th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($barbers as $barber)
                                <tr>
                                    {{-- BARBER --}}
                                    <td class="text-start fw-semibold ps-4">
                                        <span class="avatar-soft rounded-circle me-2"
                                            style="width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center">
                                            {{ inisial($barber->user->name) }}
                                        </span>
                                        {{ $barber->user->name }}
                                    </td>

                                    {{-- DAYS --}}
                                    @foreach ($days as $day)
                                        @php
                                            $shift = $barber->shifts
                                                ->where('week_number', $week)
                                                ->firstWhere('day_of_week', $day);
                                        @endphp

                                        <td class="shift-cell" data-barber="{{ $barber->id }}"
                                            data-week="{{ $week }}" data-day="{{ $day }}"
                                            data-start="{{ $shift ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '' }}"
                                            data-end="{{ $shift ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '' }}"
                                            data-libur="{{ $shift?->is_day_off ?? 0 }}" style="cursor:pointer">

                                            @if (!$shift || $shift->is_day_off)
                                                <span class="badge-off">OFF</span>
                                            @else
                                                <span class="badge rounded-pill badge-shift">
                                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                                                    –
                                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        @endforeach

    </div>

    {{-- MODAL --}}
    @include('shifts._modal')

    {{-- SCRIPT --}}
    <script>
        document.querySelectorAll('.shift-cell').forEach(cell => {
            cell.addEventListener('click', () => {

                const isLibur = cell.dataset.libur === '1';

                barber_id.value = cell.dataset.barber;
                day_of_week.value = cell.dataset.day;
                week_number.value = cell.dataset.week;

                is_day_off.value = isLibur ? '1' : '0';

                // ⬇️ FIX UTAMA DI SINI
                start_time.value = isLibur ? '' : formatTime(cell.dataset.start);
                end_time.value = isLibur ? '' : formatTime(cell.dataset.end);

                toggleTime(isLibur);

                new bootstrap.Modal(
                    document.getElementById('modalShift')
                ).show();
            });
        });

        function formatTime(t) {
            return t ? t.substring(0, 5) : '';
        }

        function toggleTime(isLibur) {
            start_time.disabled = isLibur;
            end_time.disabled = isLibur;
        }

        is_day_off.addEventListener('change', function() {
            const libur = this.value === '1';
            toggleTime(libur);

            if (libur) {
                start_time.value = '';
                end_time.value = '';
            }
        });
    </script>
@endsection

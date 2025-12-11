@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-4">Shift Saya</h3>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shifts as $s)
                    <tr>
                        <td>{{ ucfirst($s->day_of_week) }}</td>
                        <td>{{ $s->start_time ?? '-' }}</td>
                        <td>{{ $s->end_time ?? '-' }}</td>
                        <td>
                            @if ($s->is_day_off)
                                <span class="badge badge-danger">Libur</span>
                            @else
                                <span class="badge badge-success">Aktif</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h3 class="fw-bold mb-4">Manajemen Shift Barber</h3>

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif


        {{-- BUTTON TAMBAH --}}
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalTambah">
            <i class="fe fe-plus"></i> Tambah Shift
        </button>


        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Barber</th>
                            <th>Hari</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Day Off?</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($shifts as $key => $s)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $s->barber->user->name }}</td>
                                <td class="text-capitalize">{{ $s->day_of_week }}</td>
                                <td>{{ $s->start_time ?? '-' }}</td>
                                <td>{{ $s->end_time ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $s->is_day_off ? 'badge-danger' : 'badge-success' }}">
                                        {{ $s->is_day_off ? 'Libur' : 'Masuk' }}
                                    </span>
                                </td>

                                <td class="text-center">

                                    {{-- EDIT --}}
                                    <button class="btn btn-sm btn-warning" data-toggle="modal"
                                        data-target="#modalEdit{{ $s->id }}">
                                        <i class="fe fe-edit"></i>
                                    </button>

                                    {{-- DELETE --}}
                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#modalHapus{{ $s->id }}">
                                        <i class="fe fe-trash"></i>
                                    </button>

                                </td>
                            </tr>



                            {{-- ======================== MODAL EDIT ======================== --}}
                            <div class="modal fade" id="modalEdit{{ $s->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('shifts.update', $s->id) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-content">

                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title">Edit Shift</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">

                                                <label>Barber</label>
                                                <select name="barber_id" class="form-control mb-3">
                                                    @foreach ($barbers as $b)
                                                        <option value="{{ $b->id }}"
                                                            {{ $b->id == $s->barber_id ? 'selected' : '' }}>
                                                            {{ $b->user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label>Hari</label>
                                                <select name="day_of_week" class="form-control mb-3">
                                                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                        <option value="{{ $day }}"
                                                            {{ $s->day_of_week == $day ? 'selected' : '' }}>
                                                            {{ ucfirst($day) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label>Jam Mulai</label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fe fe-clock"></i></span>
                                                    </div>
                                                    <input type="time" name="start_time" value="{{ $s->start_time }}"
                                                        class="form-control">
                                                </div>

                                                <label>Jam Selesai</label>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fe fe-clock"></i></span>
                                                    </div>
                                                    <input type="time" name="end_time" value="{{ $s->end_time }}"
                                                        class="form-control">
                                                </div>

                                                <label>Day Off?</label>
                                                <select name="is_day_off" class="form-control">
                                                    <option value="0" {{ !$s->is_day_off ? 'selected' : '' }}>Masuk
                                                    </option>
                                                    <option value="1" {{ $s->is_day_off ? 'selected' : '' }}>Libur
                                                    </option>
                                                </select>

                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-warning">Update</button>
                                            </div>

                                        </div>

                                    </form>
                                </div>
                            </div>



                            {{-- ======================== MODAL DELETE ======================== --}}
                            <div class="modal fade" id="modalHapus{{ $s->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('shifts.destroy', $s->id) }}">
                                        @csrf
                                        @method('DELETE')

                                        <div class="modal-content">

                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Hapus Shift</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                Hapus shift <b>{{ ucfirst($s->day_of_week) }}</b> untuk barber:
                                                <br><b>{{ $s->barber->user->name }}</b> ?
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-danger">Hapus</button>
                                            </div>

                                        </div>

                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>




        {{-- ======================== MODAL TAMBAH ======================== --}}
        <div class="modal fade" id="modalTambah" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('shifts.store') }}">
                    @csrf

                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Tambah Shift</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">

                            <label>Barber</label>
                            <select name="barber_id" class="form-control mb-3">
                                @foreach ($barbers as $b)
                                    <option value="{{ $b->id }}">{{ $b->user->name }}</option>
                                @endforeach
                            </select>

                            <label>Hari</label>
                            <select name="day_of_week" class="form-control mb-3">
                                @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>

                            <label>Jam Mulai</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fe fe-clock"></i></span>
                                </div>
                                <input type="time" name="start_time" class="form-control">
                            </div>

                            <label>Jam Selesai</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fe fe-clock"></i></span>
                                </div>
                                <input type="time" name="end_time" class="form-control">
                            </div>

                            <label>Day Off?</label>
                            <select name="is_day_off" class="form-control">
                                <option value="0">Masuk</option>
                                <option value="1">Libur</option>
                            </select>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-primary">Simpan</button>
                        </div>

                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

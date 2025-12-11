@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <h3 class="fw-bold mb-4">Manajemen Barber</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Tombol Tambah --}}
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#modalTambah">
            <i class="fe fe-plus"></i> Tambah Barber
        </button>

        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Foto</th>
                            <th>User</th>
                            <th>Nickname</th>
                            <th>Speciality</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($barbers as $key => $b)
                            <tr>
                                <td>{{ $key + 1 }}</td>

                                {{-- Foto Barber --}}
                                <td>
                                    <img src="{{ $b->image ? asset('storage/' . $b->image) : asset('images/default-barber.jpg') }}"
                                        width="60" height="60" class="rounded">
                                </td>

                                <td>{{ $b->user->name }}</td>
                                <td>{{ $b->nickname ?? '-' }}</td>
                                <td>{{ $b->speciality ?? '-' }}</td>
                                <td>Rp {{ number_format($b->price) }}</td>

                                <td>
                                    <span class="badge {{ $b->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $b->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td class="text-center">

                                    {{-- Edit --}}
                                    <button class="btn btn-sm btn-warning" data-toggle="modal"
                                        data-target="#modalEdit{{ $b->id }}">
                                        <i class="fe fe-edit"></i>
                                    </button>

                                    {{-- Hapus --}}
                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#modalHapus{{ $b->id }}">
                                        <i class="fe fe-trash"></i>
                                    </button>

                                </td>
                            </tr>


                            {{-- ===================== MODAL EDIT ===================== --}}
                            <div class="modal fade" id="modalEdit{{ $b->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" enctype="multipart/form-data"
                                        action="{{ route('barbers.update', $b->id) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-content">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title">Edit Barber</h5>
                                                <button type="button" class="close" data-dismiss="modal">×</button>
                                            </div>

                                            <div class="modal-body">

                                                <label>User Barber</label>
                                                <select name="user_id" class="form-control mb-3">
                                                    @foreach ($users as $u)
                                                        <option value="{{ $u->id }}"
                                                            {{ $b->user_id == $u->id ? 'selected' : '' }}>
                                                            {{ $u->name }} ({{ $u->email }})
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label>Nickname</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text"><i class="fe fe-user"></i></span>
                                                    <input name="nickname" value="{{ $b->nickname }}"
                                                        class="form-control">
                                                </div>

                                                <label>Speciality</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text"><i class="fe fe-scissors"></i></span>
                                                    <input name="speciality" value="{{ $b->speciality }}"
                                                        class="form-control">
                                                </div>

                                                <label>Harga Barber</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text"><i class="fe fe-dollar-sign"></i></span>
                                                    <input name="price" type="number" value="{{ $b->price }}"
                                                        class="form-control">
                                                </div>

                                                <label>Foto Barber</label>
                                                <input type="file" name="image" class="form-control mb-3">

                                                <label>Status</label>
                                                <select name="is_active" class="form-control">
                                                    <option value="1" {{ $b->is_active ? 'selected' : '' }}>Aktif
                                                    </option>
                                                    <option value="0" {{ !$b->is_active ? 'selected' : '' }}>Nonaktif
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


                            {{-- ===================== MODAL HAPUS ===================== --}}
                            <div class="modal fade" id="modalHapus{{ $b->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('barbers.destroy', $b->id) }}">
                                        @csrf
                                        @method('DELETE')

                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Hapus Barber</h5>
                                                <button type="button" class="close" data-dismiss="modal">×</button>
                                            </div>

                                            <div class="modal-body">
                                                Hapus barber <b>{{ $b->user->name }}</b> ?
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


        {{-- ===================== MODAL TAMBAH ===================== --}}
        <div class="modal fade" id="modalTambah" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" enctype="multipart/form-data" action="{{ route('barbers.store') }}">
                    @csrf

                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Tambah Barber</h5>
                            <button type="button" class="close" data-dismiss="modal">×</button>
                        </div>

                        <div class="modal-body">

                            <label>User Barber</label>
                            <select name="user_id" class="form-control mb-3" required>
                                <option value="">-- Pilih User --</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>

                            <label>Nickname</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fe fe-user"></i></span>
                                <input name="nickname" class="form-control">
                            </div>

                            <label>Speciality</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fe fe-scissors"></i></span>
                                <input name="speciality" class="form-control">
                            </div>

                            <label>Harga Barber</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fe fe-dollar-sign"></i></span>
                                <input name="price" type="number" class="form-control" required>
                            </div>

                            <label>Foto Barber</label>
                            <input type="file" name="image" class="form-control mb-3">

                            <label>Status</label>
                            <select name="is_active" class="form-control">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
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

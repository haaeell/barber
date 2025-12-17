{{-- ================= MODAL EDIT SHIFT ================= --}}
<div class="modal fade" id="modalShift" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('shifts.store') }}" class="modal-content border-0 shadow">
            @csrf

            <input type="hidden" name="barber_id" id="barber_id">
            <input type="hidden" name="day_of_week" id="day_of_week">
            <input type="hidden" name="week_number" id="week_number">

            {{-- HEADER --}}
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fe fe-clock me-1"></i> Atur Shift Barber
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="is_day_off" id="is_day_off" class="form-select">
                        <option value="0">Masuk</option>
                        <option value="1">Libur</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Jam Mulai</label>
                        <input type="time" name="start_time" id="start_time" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Jam Selesai</label>
                        <input type="time" name="end_time" id="end_time" class="form-control">
                    </div>
                </div>

                <div class="alert alert-info small mb-0">
                    💡 Pilih <b>Libur</b> jika barber tidak masuk di hari tersebut.
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary fw-semibold">
                    <i class="fe fe-save me-1"></i> Simpan Shift
                </button>
            </div>

        </form>
    </div>
</div>

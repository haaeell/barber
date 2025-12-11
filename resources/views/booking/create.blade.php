@extends('layouts.app')

@section('content')
    {{-- FLATPICKR --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* ---------------------------
        WIZARD PROGRESS BAR
    ---------------------------- */
        .wizard-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .wizard-step {
            flex: 1;
            text-align: center;
            font-weight: 600;
            color: #aaa;
            position: relative;
        }

        .wizard-step.active {
            color: #007bff;
        }

        .wizard-step.completed {
            color: #28a745;
        }

        .wizard-step::before {
            content: attr(data-step);
            width: 34px;
            height: 34px;
            line-height: 34px;
            display: inline-block;
            background: #ddd;
            color: white;
            border-radius: 50%;
            font-size: 16px;
            margin-bottom: 6px;
        }

        .wizard-step.active::before {
            background: #007bff;
        }

        .wizard-step.completed::before {
            background: #28a745;
        }

        .wizard-step:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 17px;
            left: 55%;
            width: 90%;
            height: 3px;
            background: #ddd;
            z-index: -1;
        }

        .wizard-step.completed:not(:last-child)::after {
            background: #28a745;
        }

        /* ---------------------------
        PAGE ANIMATION SLIDE
    ---------------------------- */
        .step-page {
            display: none;
            animation-duration: 0.4s;
        }

        .step-page.active {
            display: block;
        }

        .slide-left {
            animation-name: slideLeft;
        }

        .slide-right {
            animation-name: slideRight;
        }

        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ---------------------------
        CARD STYLE
    ---------------------------- */
        .barber-card,
        .service-card {
            border-radius: 12px;
            border: 1px solid #eee;
            transition: 0.2s;
            cursor: pointer;
        }

        .barber-card:hover,
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .barber-card.selected,
        .service-card.selected {
            border: 2px solid #007bff;
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.2);
        }

        /* SLOT BUTTON */
        .slot-btn {
            min-width: 90px;
            border-radius: 10px;
            margin: 5px;
        }

        .slot-btn.active {
            background: #28a745 !important;
            color: white !important;
        }
    </style>

    <div class="container mt-4">

        {{-- PROGRESS BAR --}}
        <div class="wizard-container">
            <div class="wizard-step active" data-step="1" id="nav-step-1">Barber</div>
            <div class="wizard-step" data-step="2" id="nav-step-2">Service</div>
            <div class="wizard-step" data-step="3" id="nav-step-3">Tanggal</div>
            <div class="wizard-step" data-step="4" id="nav-step-4">Jam</div>
            <div class="wizard-step" data-step="5" id="nav-step-5">Konfirmasi</div>
        </div>

        {{-- =========================
        STEP 1 - BARBER
    ========================== --}}
        <div id="step1" class="step-page active slide-right">
            <h3>Pilih Barber</h3>
            <p class="text-muted mb-3">Pilih barber favorit Anda.</p>

            <div class="row">
                @foreach ($barbers as $b)
                    <div class="col-md-4 mb-3">
                        <div class="barber-card p-2 shadow-sm" data-id="{{ $b->id }}"
                            data-name="{{ $b->user->name }}" data-price="{{ $b->price }}">
                            <img src="{{ asset('storage/' . ($b->image ?? 'default-barber.jpg')) }}"
                                class="w-100 rounded mb-2" style="height:160px;object-fit:cover;">
                            <h5 class="text-center">{{ $b->user->name }}</h5>
                            <p class="text-center text-muted mb-1">
                                Rp {{ number_format($b->price) }}
                            </p>
                            <button class="btn btn-primary btn-sm w-100 mt-1 next-from-barber">
                                Pilih Barber
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- =========================
        STEP 2 - SERVICE
    ========================== --}}
        <div id="step2" class="step-page">
            <h3>Pilih Service</h3>
            <p class="text-muted mb-3">Pilih layanan yang Anda inginkan.</p>

            <div class="row">
                @foreach ($services as $s)
                    <div class="col-md-4 mb-3">
                        <div class="service-card p-2 shadow-sm" data-id="{{ $s->id }}"
                            data-name="{{ $s->name }}" data-price="{{ $s->price }}"
                            data-duration="{{ $s->duration }}">
                            <img src="{{ asset('storage/' . ($s->image ?? 'default-service.jpg')) }}"
                                class="w-100 rounded mb-2" style="height:160px;object-fit:cover;">
                            <h5 class="text-center">{{ $s->name }}</h5>
                            <p class="text-muted text-center mb-1">{{ $s->duration }} menit</p>
                            <p class="text-center text-muted">Rp {{ number_format($s->price) }}</p>
                            <button class="btn btn-primary btn-sm w-100 mt-1 next-from-service">
                                Pilih Service
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- =========================
        STEP 3 - TANGGAL
    ========================== --}}
        <div id="step3" class="step-page">
            <h3>Pilih Tanggal</h3>
            <p class="text-muted">Pilih tanggal sesuai jadwal barber.</p>

            <input type="text" id="date" class="form-control col-md-4 mb-4" placeholder="Pilih tanggal...">

            <button class="btn btn-primary next-step-3">Lanjut</button>
        </div>

        {{-- =========================
        STEP 4 - JAM
    ========================== --}}
        <div id="step4" class="step-page">
            <h3>Pilih Jam</h3>
            <p class="text-muted">Slot kosong berdasarkan shift barber & durasi.</p>

            <div id="slotContainer" class="d-flex flex-wrap"></div>
        </div>

        {{-- =========================
        STEP 5 - KONFIRMASI
    ========================== --}}
        <div id="step5" class="step-page">
            <h3>Konfirmasi Booking</h3>

            <div class="card p-3 shadow-sm">
                <p><b>Barber:</b> <span id="summaryBarber">-</span></p>
                <p><b>Service:</b> <span id="summaryService">-</span></p>
                <p><b>Tanggal:</b> <span id="summaryDate">-</span></p>
                <p><b>Jam:</b> <span id="summaryTime">-</span></p>

                <hr>

                <h4>Total:</h4>
                <h2 id="summaryTotal" class="text-primary">Rp 0</h2>

                <form method="POST" action="{{ route('booking.store') }}">
                    @csrf

                    <input type="hidden" name="barber_id" id="formBarber">
                    <input type="hidden" name="service_id" id="formService">
                    <input type="hidden" name="date" id="formDate">
                    <input type="hidden" name="time" id="formTime">

                    <button class="btn btn-success w-100 mt-3">Konfirmasi Booking</button>
                </form>
            </div>
        </div>
    </div>

    {{-- =========================
    JAVASCRIPT — WIZARD LOGIC
========================= --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        let currentStep = 1;
        let barberPrice = 0;
        let servicePrice = 0;

        /* -----------------------
            WIZARD HELPER
        ------------------------ */
        function goToStep(step, direction = 'left') {
            document.querySelector(`#step${currentStep}`).classList.remove("active");
            document.querySelector(`#nav-step-${currentStep}`).classList.remove("active");

            document.querySelector(`#nav-step-${currentStep}`).classList.add("completed");

            currentStep = step;

            let newPage = document.querySelector(`#step${step}`);
            newPage.classList.add("active");
            newPage.classList.add(direction === 'left' ? "slide-left" : "slide-right");

            document.querySelector(`#nav-step-${step}`).classList.add("active");
        }

        /* -----------------------
            STEP 1 - PILIH BARBER
        ------------------------ */
        document.querySelectorAll(".next-from-barber").forEach(btn => {
            btn.onclick = function() {
                const parent = this.closest(".barber-card");

                document.querySelectorAll(".barber-card").forEach(c => c.classList.remove("selected"));
                parent.classList.add("selected");

                barberPrice = parseInt(parent.dataset.price);

                document.getElementById("formBarber").value = parent.dataset.id;
                document.getElementById("summaryBarber").innerText = parent.dataset.name;

                goToStep(2);
            };
        });

        /* -----------------------
            STEP 2 - PILIH SERVICE
        ------------------------ */
        document.querySelectorAll(".next-from-service").forEach(btn => {
            btn.onclick = function() {
                const parent = this.closest(".service-card");

                document.querySelectorAll(".service-card").forEach(c => c.classList.remove("selected"));
                parent.classList.add("selected");

                servicePrice = parseInt(parent.dataset.price);

                document.getElementById("formService").value = parent.dataset.id;
                document.getElementById("summaryService").innerText = parent.dataset.name;

                document.getElementById("summaryTotal").innerText =
                    "Rp " + (barberPrice + servicePrice).toLocaleString();

                goToStep(3);
            };
        });

        /* -----------------------
            STEP 3 - TANGGAL
        ------------------------ */
        flatpickr("#date", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                document.getElementById("summaryDate").innerText = dateStr;
            }
        });

        document.querySelector(".next-step-3").onclick = function() {
            const date = document.getElementById("date").value;
            if (!date) return alert("Pilih tanggal dulu");

            document.getElementById("formDate").value = date;

            loadSlots();
            goToStep(4);
        };

        /* -----------------------
            STEP 4 - LOAD JAM
        ------------------------ */
        function loadSlots() {
            let barber = document.getElementById("formBarber").value;
            let service = document.getElementById("formService").value;
            let date = document.getElementById("formDate").value;

            fetch("/booking/slots", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        barber_id: barber,
                        service_id: service,
                        date: date
                    })
                })
                .then(res => res.json())
                .then(data => {
                    let container = document.getElementById("slotContainer");
                    container.innerHTML = "";

                    data.slots.forEach(slot => {
                        let btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "slot-btn btn btn-outline-success";
                        btn.innerText = slot;

                        btn.onclick = function() {
                            document.querySelectorAll(".slot-btn").forEach(b => b.classList.remove(
                                "active"));
                            btn.classList.add("active");

                            document.getElementById("formTime").value = slot;
                            document.getElementById("summaryTime").innerText = slot;

                            goToStep(5);
                        };

                        container.appendChild(btn);
                    });
                });
        }
    </script>
@endsection

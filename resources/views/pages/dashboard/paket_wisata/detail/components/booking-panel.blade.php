{{-- Kolom Kanan: Panel Booking (4 Kolom) --}}
<aside class="w-full space-y-4 xl:sticky xl:top-24 xl:self-start xl:col-span-4">

    @php
        $unitPrice = (float) $package->price;
        $minimumPax = 1;
        $availableTrips = $tripOptions->values();
        $firstTripQuota = $firstTrip ? max(0, (int) $firstTrip->remaining_seats) : 20;
        $initialPax = 1;
        $downPaymentRate = 30;
        $downPaymentPerPerson = $unitPrice * $downPaymentRate / 100;
        $initialTotal = $unitPrice * $initialPax;
        $initialDownPayment = $initialTotal * $downPaymentRate / 100;
        $initialRemaining = $initialTotal - $initialDownPayment;
    @endphp

    <section class="rounded-2xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6">

        {{-- FLASH MESSAGE / NOTIFIKASI KUOTA DARI SERVER --}}
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 p-3.5 text-xs text-rose-900 shadow-sm">
                <div class="flex items-start gap-2.5">
                    <span class="text-base text-rose-600 leading-none">⚠️</span>
                    <div>
                        <p class="font-bold text-rose-800">Peringatan Kuota Tiket:</p>
                        <p class="mt-1 leading-relaxed text-slate-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- LIVE ALERT KUOTA BERUBAH / HABIS KARENA USER LAIN BOOKING DULUAN --}}
        <div id="liveQuotaAlert" class="hidden mb-4 rounded-xl border border-amber-300 bg-amber-50 p-3.5 text-xs text-amber-900 shadow-sm">
            <div class="flex items-start gap-2.5">
                <span class="text-base text-amber-600 leading-none">⚠️</span>
                <div>
                    <p class="font-bold text-amber-800" id="liveQuotaAlertTitle">Informasi Kuota Terkini:</p>
                    <p class="mt-1 leading-relaxed text-amber-950" id="liveQuotaAlertText"></p>
                </div>
            </div>
        </div>

        {{-- HARGA PAKET --}}
        <div class="mb-5">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                Harga Paket Reguler
            </p>

            <div class="mt-1 flex items-end justify-between gap-3">
                <div>
                    <span class="block text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">
                        Rp
                    </span>

                    <p class="text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl">
                        {{ number_format($unitPrice, 0, ',', '.') }}
                    </p>
                </div>

                <span class="mb-2 text-sm text-slate-500">/ orang</span>
            </div>

            <p class="mt-2 text-xs font-semibold text-teal-700">
                All-in • Harga per peserta
            </p>
        </div>

        {{-- INFORMASI DP --}}
        <div class="mb-5 rounded-xl bg-blue-50 p-4">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-medium text-teal-800">
                    Uang Muka (DP) Ringan:
                </p>

                <span class="rounded-full bg-teal-200 px-2 py-1 text-xs font-bold text-teal-800">
                    {{ $downPaymentRate }}%
                </span>
            </div>

            <div class="mt-1 flex flex-wrap items-baseline gap-1">
                <span class="text-2xl font-extrabold text-teal-700">
                    Rp {{ number_format($downPaymentPerPerson, 0, ',', '.') }}
                </span>

                <span class="text-xs text-slate-600">/ pax</span>
            </div>

            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Amankan jadwal perjalanan Anda dengan membayar uang muka.
                Sisa pelunasan dibayarkan sesuai ketentuan pembayaran paket.
            </p>
        </div>

        {{-- FORM PILIHAN BOOKING --}}
        <form
            id="packageBookingForm"
            action="{{ route('booking.store') }}"
            method="POST"
            class="space-y-4"
        >
            @csrf

            <input type="hidden" name="package_id" value="{{ $package->id }}">
            <input type="hidden" name="selected_armada" id="inputArmada" value="Innova Reborn">

            {{-- TANGGAL KEBERANGKATAN --}}
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <label for="trip_id" class="block text-sm font-semibold text-slate-800">
                        Pilih Tanggal Keberangkatan
                    </label>
                </div>

                <div class="relative">
                    <select
                        id="trip_id"
                        name="trip_id"
                        required
                        class="w-full rounded-xl border-0 bg-blue-50 px-4 py-3 pr-9 text-sm text-slate-700 outline-none ring-1 ring-blue-100 focus:ring-2 focus:ring-teal-600"
                    >
                        <option value="">Pilih jadwal keberangkatan</option>

                        @foreach ($availableTrips as $trip)
                            @if (!empty($trip->departure_date))
                                <option
                                    value="{{ $trip->id }}"
                                    data-date="{{ \Illuminate\Support\Carbon::parse($trip->departure_date)->format('Y-m-d') }}"
                                    data-formatted-date="{{ \Illuminate\Support\Carbon::parse($trip->departure_date)->translatedFormat('d F Y') }}"
                                    data-quota="{{ $trip->remaining_seats }}"
                                    data-total-quota="{{ $trip->quota }}"
                                    data-booked="{{ $trip->booked_seats }}"
                                    data-batch="{{ $trip->batch_name }}"
                                    data-check-url="{{ route('trips.check-quota', $trip->id) }}"
                                    @selected($firstTrip && $firstTrip->id === $trip->id)
                                >
                                    {{ \Illuminate\Support\Carbon::parse($trip->departure_date)->translatedFormat('d F Y') }} (Sisa {{ $trip->remaining_seats }} Kursi)
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- BADGE STATUS SISA KUOTA DINAMIS --}}
                <div
                    id="quotaStatusBadge"
                    class="mt-2.5 flex items-center justify-between rounded-xl bg-teal-50 px-3.5 py-2.5 text-xs font-semibold text-teal-900 border border-teal-200"
                >
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-teal-500"></span>
                        </span>
                        <span id="quotaBatchText">Batch {{ $firstTrip->batch_name ?? 'B1' }}</span>
                    </div>

                    <div class="flex items-center gap-1 font-bold">
                        <span>Sisa Kuota:</span>
                        <span id="quotaSeatsText" class="text-teal-700 font-extrabold text-sm">
                            {{ $firstTripQuota }} Kursi
                        </span>
                    </div>
                </div>

                @if ($availableTrips->isEmpty())
                    <p class="mt-2 text-xs text-amber-700 font-medium">
                        Belum ada jadwal keberangkatan yang tersedia. Silakan hubungi admin.
                    </p>
                @endif
            </div>

            {{-- JUMLAH PESERTA --}}
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <label for="inputPax" class="block text-sm font-semibold text-slate-800">
                        Jumlah Peserta (Pax)
                    </label>

                    <span
                        id="maxPaxBadge"
                        class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-bold text-teal-800 border border-teal-200"
                    >
                        Maks: <span id="maxPaxText">{{ $firstTripQuota }}</span> Pax
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-blue-50 p-2">
                    <button
                        type="button"
                        id="decreasePax"
                        aria-label="Kurangi peserta"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        −
                    </button>

                    <input
                        id="inputPax"
                        name="pax"
                        type="number"
                        min="1"
                        max="{{ max(1, $firstTripQuota) }}"
                        value="{{ $initialPax }}"
                        required
                        class="w-24 border-0 bg-transparent text-center text-base font-bold text-slate-800 outline-none focus:ring-0"
                    >

                    <button
                        type="button"
                        id="increasePax"
                        aria-label="Tambah peserta"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        +
                    </button>
                </div>

                <p class="mt-1.5 flex items-center justify-between text-xs text-slate-500">
                    <span>Minimal 1 peserta.</span>
                    <span class="text-slate-400">Disesuaikan sisa kuota trip admin.</span>
                </p>

                {{-- NOTIFIKASI BATAS KUOTA PESERTA --}}
                <div
                    id="paxLimitAlert"
                    class="hidden mt-2 rounded-xl border border-rose-200 bg-rose-50 p-2.5 text-xs text-rose-800 flex items-start gap-2"
                >
                    <span class="font-bold text-sm leading-none text-rose-600">⚠️</span>
                    <span id="paxLimitMessage" class="leading-relaxed"></span>
                </div>
            </div>

            {{-- PILIHAN ARMADA --}}
            <div>
                <p class="mb-2 text-sm font-semibold text-slate-800">
                    Pilihan Armada
                </p>

                <div class="rounded-xl bg-blue-50 p-1">
                    <div class="grid grid-cols-2 gap-1">
                        <button
                            type="button"
                            id="btnArmadaInnova"
                            class="armada-btn rounded-lg bg-white px-2 py-3 text-xs font-semibold text-slate-700 shadow-sm"
                            onclick="selectArmada('Innova Reborn')"
                        >
                            Innova Reborn
                        </button>

                        <button
                            type="button"
                            id="btnArmadaHiAce"
                            class="armada-btn rounded-lg px-2 py-3 text-xs font-semibold text-slate-600"
                            onclick="selectArmada('HiAce Commuter')"
                        >
                            HiAce Commuter
                        </button>
                    </div>
                </div>

                <p class="mt-2 text-xs leading-relaxed text-slate-500">
                    Pilihan armada dan kapasitas perlu dikonfirmasi berdasarkan
                    ketersediaan kendaraan untuk jadwal yang dipilih.
                </p>
            </div>

            {{-- RINGKASAN PEMBAYARAN --}}
            <div class="rounded-xl border border-slate-100 p-4">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-slate-600">
                        Total Paket (<span id="summaryPax">{{ $initialPax }}</span> Pax)
                    </span>

                    <strong id="displayTotal" class="text-slate-900">
                        Rp {{ number_format($initialTotal, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                    <span class="font-semibold text-teal-700">
                        DP ({{ $downPaymentRate }}%)
                    </span>

                    <strong id="displayDP" class="text-teal-700">
                        Rp {{ number_format($initialDownPayment, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                    <span class="text-slate-500">Sisa Pelunasan</span>

                    <span id="displayRemaining" class="font-medium text-slate-500">
                        Rp {{ number_format($initialRemaining, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- TOMBOL LANJUT KE FORMULIR BOOKING (DIUBAH MENJADI ANCHOR TAG) --}}
            <a
                id="continueToBookingForm"
                href="{{ route('booking.form', ['slug' => $package->slug, 'trip_id' => $firstTrip?->id, 'pax' => $initialPax, 'selected_armada' => 'Innova Reborn']) }}"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-4 text-base font-bold text-white shadow-sm transition hover:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:ring-offset-2"
            >
                Lanjut ke Formulir Booking
                <span aria-hidden="true">&rarr;</span>
            </a>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const link = document.getElementById('continueToBookingForm');
                    const tripSelect = document.getElementById('trip_id');
                    const paxInput = document.getElementById('inputPax');
                    const armadaInput = document.getElementById('inputArmada');

                    if (!link || !tripSelect || !paxInput || !armadaInput) return;

                    link.addEventListener('click', function (event) {
                        const tripId = tripSelect.value;
                        const pax = Number.parseInt(paxInput.value, 10);
                        const selectedArmada = armadaInput.value;

                        if (!tripId || !Number.isInteger(pax) || pax < 1) {
                            event.preventDefault();
                            alert('Silakan pilih jadwal keberangkatan dan jumlah peserta terlebih dahulu.');
                            return;
                        }

                        const target = new URL(link.href, window.location.origin);
                        target.searchParams.set('trip_id', tripId);
                        target.searchParams.set('pax', String(pax));
                        target.searchParams.set('selected_armada', selectedArmada);
                        link.href = target.toString();
                    });
                });
            </script>

            {{-- WHATSAPP --}}
            <a
                href="https://wa.me/{{ preg_replace('/\D/', '', config('services.whatsapp.number', '')) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:bg-blue-100"
            >
                <span class="text-teal-700" aria-hidden="true">▣</span>
                Tanya Jadwal via WhatsApp CS
            </a>

            <div class="flex items-start justify-center gap-2 pt-1 text-center text-xs leading-relaxed text-slate-500">
                <span class="text-teal-700" aria-hidden="true">♙</span>
                <p>
                    Garansi Reschedule H-3 • Konfirmasi Instan • Tanpa Biaya Tersembunyi
                </p>
            </div>

        </form>
    </section>

    {{-- KARTU BANTUAN --}}
    <section class="flex items-start gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-lg text-teal-700">
            ?
        </div>

        <div>
            <p class="text-sm font-semibold text-slate-800">
                Butuh Kustomisasi Rute?
            </p>

            <p class="mt-1 text-sm leading-relaxed text-slate-600">
                Kami melayani penyesuaian hotel, durasi, atau penjemputan.
                Silakan hubungi tim kami.
            </p>
        </div>
    </section>

</aside>

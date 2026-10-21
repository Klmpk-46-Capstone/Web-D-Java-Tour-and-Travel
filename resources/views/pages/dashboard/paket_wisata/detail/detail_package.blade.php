{{-- resources/views/pages/customer/paket_wisata/detail/detail_package.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $package->name }} - D'Java Tour &amp; Travel</title>
    <meta name="description" content="{{ $package->description ?: 'Detail paket wisata D’Java Tour & Travel.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'DM Sans', Arial, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    @php
        $formatRupiah = static fn($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');

        $imageUrl = static function ($value) {
            if (!is_string($value) || trim($value) === '') {
                return null;
            }

            if (
                filter_var($value, FILTER_VALIDATE_URL) &&
                in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)
            ) {
                return $value;
            }

            if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
                return asset('storage/' . ltrim($value, '/'));
            }

            return null;
        };

        $mainImage = $imageUrl($package->image_url ?? null);
        $tripOptions = $package->trips
            ->filter(
                fn($trip) => in_array($trip->status, ['available', 'open', 'almost_full'], true) &&
                    $trip->departure_date &&
                    \Illuminate\Support\Carbon::parse($trip->departure_date)
                        ->startOfDay()
                        ->gte(now()->startOfDay()) &&
                    (int) $trip->booked_seats < (int) $trip->quota,
            )
            ->sortBy('departure_date');

        $firstTrip = $tripOptions->first();
        $reviews = $package->reviews->where('is_approved', true)->where('is_hidden', false)->sortByDesc('created_at');

        $averageRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : (float) ($package->rating ?? 0);

        $vehicleList = isset($vehicles) ? collect($vehicles)->where('is_active', true) : collect();

        $includes = [
            'Transportasi sesuai ketentuan paket',
            'Pengemudi atau pemandu sesuai paket',
            'Rencana perjalanan sesuai itinerary',
            'Informasi keberangkatan dari pengelola',
        ];

        $excludes = [
            'Pengeluaran pribadi di luar paket',
            'Biaya tambahan yang tidak tercantum',
            'Aktivitas opsional di luar itinerary',
        ];
    @endphp

    @include('components.navbar.navbar')

    <main class="mx-auto w-full max-w-screen-2xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- FLASH MESSAGES NOTIFIKASI --}}
        @if (session('error'))
            <div
                class="mb-5 flex items-start justify-between gap-3 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="text-2xl leading-none">⚠️</span>
                    <div>
                        <p class="font-extrabold text-rose-800">Pemberitahuan Kuota Tiket</p>
                        <p class="mt-0.5 text-slate-700 leading-relaxed">{{ session('error') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-rose-500 hover:text-rose-800 text-xl font-bold leading-none">&times;</button>
            </div>
        @endif

        @if (session('success'))
            <div
                class="mb-5 flex items-start justify-between gap-3 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-900 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="text-2xl leading-none">✓</span>
                    <div>
                        <p class="font-extrabold text-emerald-800">Berhasil</p>
                        <p class="mt-0.5 text-slate-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-emerald-500 hover:text-emerald-800 text-xl font-bold leading-none">&times;</button>
            </div>
        @endif

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('packages.index') }}" class="font-medium transition hover:text-teal-700">
                <span aria-hidden="true">&larr;</span> Kembali ke Paket Wisata
            </a>
            <span aria-hidden="true">/</span>
            <span>Paket Wisata</span>
            <span aria-hidden="true">/</span>
            <span class="font-medium text-slate-700">{{ $package->name }}</span>
        </nav>

        {{-- Package heading --}}
        <section class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                @if ($package->badge)
                    <span
                        class="mb-3 inline-flex rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                        {{ $package->badge }}
                    </span>
                @endif

                <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                    {{ $package->name }}
                </h1>

                <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
                    {{ $package->description ?: 'Informasi perjalanan dan fasilitas paket wisata tersedia di halaman ini.' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-sm">
                <span
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-100 px-3 py-2 font-semibold text-blue-900">
                    <span aria-hidden="true">◷</span>
                    {{ $package->duration ?: 'Durasi mengikuti paket' }}
                </span>
                <span
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-100 px-3 py-2 font-semibold text-blue-900">
                    <span aria-hidden="true">⌖</span>
                    {{ $package->category }}
                </span>
                <span
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-100 px-3 py-2 font-semibold text-amber-900">
                    <span aria-hidden="true">★</span>
                    {{ number_format($averageRating, 1) }}
                    ({{ $reviews->count() }} ulasan)
                </span>
            </div>
        </section>

        {{-- Main content and booking panel --}}
        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">

            {{-- Kolom Kiri: Detail, Galeri, Itinerary, Ulasan (8 Kolom) --}}
            <div class="space-y-6 xl:col-span-8">

                @include('pages.dashboard.paket_wisata.detail.components.gallery')

                @include('pages.dashboard.paket_wisata.detail.components.vehicles')

                @include('pages.dashboard.paket_wisata.detail.components.itinerary')

                @include('pages.dashboard.paket_wisata.detail.components.facilities')

                @include('pages.dashboard.paket_wisata.detail.components.reviews')

            </div>

            @include('pages.dashboard.paket_wisata.detail.components.booking-panel')

        </div>

    </main>

    <script>
        // Pemilihan Armada
        window.selectArmada = function(armadaName) {
            const input = document.getElementById('inputArmada');
            if (input) input.value = armadaName;

            const btnInnova = document.getElementById('btnArmadaInnova');
            const btnHiAce = document.getElementById('btnArmadaHiAce');

            if (btnInnova && btnHiAce) {
                if (armadaName === 'Innova Reborn') {
                    btnInnova.className =
                        'armada-btn rounded-lg bg-white px-2 py-3 text-xs font-semibold text-slate-700 shadow-sm';
                    btnHiAce.className = 'armada-btn rounded-lg px-2 py-3 text-xs font-semibold text-slate-600';
                } else {
                    btnHiAce.className =
                        'armada-btn rounded-lg bg-white px-2 py-3 text-xs font-semibold text-slate-700 shadow-sm';
                    btnInnova.className = 'armada-btn rounded-lg px-2 py-3 text-xs font-semibold text-slate-600';
                }
            }

            const modalArmada = document.getElementById('modalSummaryArmada');
            if (modalArmada) modalArmada.textContent = armadaName;
        };

        document.addEventListener('DOMContentLoaded', function() {
            const unitPrice = Number('{{ (float) ($package->price ?? 0) }}');
            const minimumPax = 1;
            const dpRate = 30;

            const tripSelect = document.getElementById('trip_id');
            const paxInput = document.getElementById('inputPax');
            const decreaseButton = document.getElementById('decreasePax');
            const increaseButton = document.getElementById('increasePax');
            const totalDisplay = document.getElementById('displayTotal');
            const dpDisplay = document.getElementById('displayDP');
            const remainingDisplay = document.getElementById('displayRemaining');
            const summaryPax = document.getElementById('summaryPax');
            const maxPaxText = document.getElementById('maxPaxText');
            const quotaSeatsText = document.getElementById('quotaSeatsText');
            const quotaBatchText = document.getElementById('quotaBatchText');
            const quotaStatusBadge = document.getElementById('quotaStatusBadge');
            const paxLimitAlert = document.getElementById('paxLimitAlert');
            const paxLimitMessage = document.getElementById('paxLimitMessage');
            const liveQuotaAlert = document.getElementById('liveQuotaAlert');
            const liveQuotaAlertText = document.getElementById('liveQuotaAlertText');
            const btnBookingBtn = document.getElementById('btnBookingForm') || document.getElementById(
                'btnOpenBookingModal');
            const btnBookingText = document.getElementById('btnBookingText');
            const bookingModal = document.getElementById('bookingModal');
            const btnCloseModal = document.getElementById('btnCloseModal');
            const btnCancelModal = document.getElementById('btnCancelModal');

            if (!paxInput || !totalDisplay || !dpDisplay || !remainingDisplay) {
                return;
            }

            function formatRupiah(value) {
                return 'Rp ' + Math.round(value).toLocaleString('id-ID');
            }

            // Dapatkan info trip yang saat ini dipilih
            function getSelectedTripInfo() {
                if (!tripSelect || tripSelect.selectedIndex < 0) {
                    return {
                        id: null,
                        quota: 20,
                        batch: 'B1',
                        formattedDate: '',
                        checkUrl: ''
                    };
                }
                const opt = tripSelect.options[tripSelect.selectedIndex];
                if (!opt || !opt.value) {
                    return {
                        id: null,
                        quota: 0,
                        batch: '-',
                        formattedDate: '',
                        checkUrl: ''
                    };
                }
                return {
                    id: opt.value,
                    quota: Math.max(0, parseInt(opt.dataset.quota, 10) || 0),
                    batch: opt.dataset.batch || 'B1',
                    formattedDate: opt.dataset.formattedDate || opt.text,
                    checkUrl: opt.dataset.checkUrl || ''
                };
            }

            // Perbarui tampilan badge kuota & batas max pax
            function updateQuotaDisplay(tripInfo) {
                const quota = tripInfo.quota;

                if (maxPaxText) {
                    maxPaxText.textContent = quota;
                }
                if (quotaBatchText) {
                    quotaBatchText.textContent = 'Batch ' + tripInfo.batch;
                }
                if (quotaSeatsText) {
                    quotaSeatsText.textContent = quota + ' Kursi';
                }

                paxInput.max = Math.max(1, quota);

                if (quotaStatusBadge) {
                    if (quota <= 0) {
                        quotaStatusBadge.className =
                            'mt-2.5 flex items-center justify-between rounded-xl bg-rose-50 px-3.5 py-2.5 text-xs font-semibold text-rose-900 border border-rose-200';
                        quotaSeatsText.className = 'text-rose-700 font-extrabold text-sm';
                        quotaSeatsText.textContent = 'Habis (0 Kursi)';
                    } else if (quota <= 5) {
                        quotaStatusBadge.className =
                            'mt-2.5 flex items-center justify-between rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs font-semibold text-amber-900 border border-amber-200';
                        quotaSeatsText.className = 'text-amber-700 font-extrabold text-sm';
                        quotaSeatsText.textContent = 'Sisa ' + quota + ' Kursi!';
                    } else {
                        quotaStatusBadge.className =
                            'mt-2.5 flex items-center justify-between rounded-xl bg-teal-50 px-3.5 py-2.5 text-xs font-semibold text-teal-900 border border-teal-200';
                        quotaSeatsText.className = 'text-teal-700 font-extrabold text-sm';
                        quotaSeatsText.textContent = quota + ' Kursi';
                    }
                }

                // Jika kuota kosong, disable tombol booking
                if (btnBookingBtn && btnBookingText) {
                    if (quota <= 0) {
                        btnBookingBtn.classList.add('pointer-events-none', 'opacity-50', 'cursor-not-allowed');
                        btnBookingText.textContent = 'Kuota Jadwal Ini Penuh';
                    } else {
                        btnBookingBtn.classList.remove('pointer-events-none', 'opacity-50', 'cursor-not-allowed');
                        btnBookingText.textContent = 'Lanjut ke Formulir Booking';
                        if (tripInfo.id) {
                            btnBookingBtn.href =
                                `{{ url('/paket-wisata/' . $package->slug . '/formulir') }}?trip_id=${tripInfo.id}`;
                        }
                    }
                }
            }

            function calculateTotal() {
                const tripInfo = getSelectedTripInfo();
                const maxQuota = tripInfo.quota;

                let pax = Number.parseInt(paxInput.value, 10);

                if (!Number.isFinite(pax) || pax < minimumPax) {
                    pax = minimumPax;
                }

                if (maxQuota > 0 && pax > maxQuota) {
                    pax = maxQuota;
                    showPaxWarning('Jumlah peserta disesuaikan ke kuota maksimal (' + maxQuota + ' kursi).');
                } else if (pax <= maxQuota) {
                    hidePaxWarning();
                }

                if (maxQuota <= 0) {
                    pax = 0;
                }

                paxInput.value = pax;

                const total = unitPrice * pax;
                const dp = total * dpRate / 100;
                const remaining = total - dp;

                totalDisplay.textContent = formatRupiah(total);
                dpDisplay.textContent = formatRupiah(dp);
                remainingDisplay.textContent = formatRupiah(remaining);

                if (summaryPax) {
                    summaryPax.textContent = pax;
                }

                decreaseButton.disabled = pax <= minimumPax || maxQuota <= 0;
                increaseButton.disabled = pax >= maxQuota || maxQuota <= 0;

                decreaseButton.classList.toggle('opacity-40', decreaseButton.disabled);
                increaseButton.classList.toggle('opacity-40', increaseButton.disabled);
            }

            function showPaxWarning(msg) {
                if (paxLimitAlert && paxLimitMessage) {
                    paxLimitMessage.textContent = msg;
                    paxLimitAlert.classList.remove('hidden');
                }
            }

            function hidePaxWarning() {
                if (paxLimitAlert) {
                    paxLimitAlert.classList.add('hidden');
                }
            }

            // Event saat pilihan tanggal berubah
            if (tripSelect) {
                tripSelect.addEventListener('change', function() {
                    const info = getSelectedTripInfo();
                    updateQuotaDisplay(info);

                    if (info.quota > 0 && Number.parseInt(paxInput.value, 10) > info.quota) {
                        paxInput.value = info.quota;
                        showPaxWarning('Jumlah peserta disesuaikan menjadi ' + info.quota +
                            ' pax sesuai sisa kuota tanggal ini.');
                    } else if (info.quota <= 0) {
                        paxInput.value = 0;
                        showPaxWarning('Maaf, kuota untuk jadwal batch ' + info.batch + ' sudah penuh.');
                    } else {
                        hidePaxWarning();
                    }

                    if (liveQuotaAlert) {
                        liveQuotaAlert.classList.add('hidden');
                    }

                    calculateTotal();
                });
            }

            // Tombol Kurang (-)
            decreaseButton.addEventListener('click', function() {
                const currentVal = Number.parseInt(paxInput.value, 10) || 1;
                if (currentVal > minimumPax) {
                    paxInput.value = currentVal - 1;
                    hidePaxWarning();
                    calculateTotal();
                }
            });

            // Tombol Tambah (+)
            increaseButton.addEventListener('click', function() {
                const tripInfo = getSelectedTripInfo();
                const maxQuota = tripInfo.quota;
                const currentVal = Number.parseInt(paxInput.value, 10) || 1;

                if (currentVal >= maxQuota) {
                    showPaxWarning('Batas maksimal peserta untuk tanggal ini adalah ' + maxQuota +
                        ' kursi (sesuai sisa kuota yang dibuat di admin).');
                } else {
                    paxInput.value = currentVal + 1;
                    hidePaxWarning();
                    calculateTotal();
                }
            });

            paxInput.addEventListener('input', function() {
                const tripInfo = getSelectedTripInfo();
                const val = Number.parseInt(paxInput.value, 10);
                if (val > tripInfo.quota) {
                    showPaxWarning('Jumlah peserta melebihi sisa kuota (' + tripInfo.quota +
                        ' kursi). Otomatis dibatasi ke ' + tripInfo.quota + ' pax.');
                } else {
                    hidePaxWarning();
                }
                calculateTotal();
            });

            paxInput.addEventListener('change', calculateTotal);

            // Handler Tombol Lanjut ke Formulir Booking dengan Pengecekan Kuota Realtime
            if (btnBookingBtn) {
                btnBookingBtn.addEventListener('click', function(e) {
                    const tripInfo = getSelectedTripInfo();

                    if (!tripInfo.id) {
                        e.preventDefault();
                        alert('Silakan pilih tanggal keberangkatan terlebih dahulu.');
                        tripSelect?.focus();
                        return;
                    }

                    if (tripInfo.quota <= 0) {
                        e.preventDefault();
                        alert('Maaf, kuota untuk jadwal tanggal ini sudah penuh.');
                        return;
                    }

                    e.preventDefault();

                    // Tampilkan status loading ringan
                    if (btnBookingText) btnBookingText.textContent = 'Memeriksa ketersediaan kuota...';

                    const requestedPax = Number.parseInt(paxInput.value, 10) || 1;
                    const targetUrl = new URL(btnBookingBtn.href ||
                        `{{ url('/paket-wisata/' . $package->slug . '/formulir') }}`);
                    targetUrl.searchParams.set('trip_id', tripInfo.id);
                    targetUrl.searchParams.set('pax', requestedPax);

                    // Cek kuota langsung ke backend untuk mengantisipasi race condition
                    if (tripInfo.checkUrl) {
                        fetch(tripInfo.checkUrl)
                            .then(response => {
                                if (!response.ok) throw new Error('Network error');
                                return response.json();
                            })
                            .then(data => {
                                if (btnBookingText) btnBookingText.textContent =
                                    'Lanjut ke Formulir Booking';

                                const liveSeats = data.remaining_seats;

                                // Update data-quota pada option agar sinkron
                                const opt = tripSelect.options[tripSelect.selectedIndex];
                                if (opt) {
                                    opt.dataset.quota = liveSeats;
                                    opt.text = `${data.formatted_date} (Sisa ${liveSeats} Kursi)`;
                                }

                                updateQuotaDisplay({
                                    id: data.id,
                                    quota: liveSeats,
                                    batch: data.batch_name,
                                    formattedDate: data.formatted_date,
                                    checkUrl: tripInfo.checkUrl
                                });

                                // Skenario 1: Kuota tiba-tiba habis oleh bookingan user lain
                                if (liveSeats <= 0) {
                                    if (liveQuotaAlert && liveQuotaAlertText) {
                                        liveQuotaAlert.classList.remove('hidden');
                                        liveQuotaAlertText.textContent =
                                            `Maaf, kuota untuk jadwal tanggal ${data.formatted_date} (Batch ${data.batch_name}) baru saja habis karena telah dibooking oleh pengguna lain. Silakan pilih tanggal keberangkatan lainnya.`;
                                    }
                                    paxInput.value = 0;
                                    calculateTotal();
                                    return;
                                }

                                // Skenario 2: Kuota berkurang dan lebih sedikit dari permintaan pax
                                if (liveSeats < requestedPax) {
                                    if (liveQuotaAlert && liveQuotaAlertText) {
                                        liveQuotaAlert.classList.remove('hidden');
                                        liveQuotaAlertText.textContent =
                                            `Perhatian: Kuota baru saja terisi oleh pengguna lain. Sisa kuota saat ini tinggal ${liveSeats} kursi. Jumlah peserta Anda disesuaikan menjadi ${liveSeats} pax.`;
                                    }
                                    paxInput.value = liveSeats;
                                    calculateTotal();
                                    targetUrl.searchParams.set('pax', liveSeats);
                                    return;
                                }

                                // Skenario 3: Kuota aman, redirect ke formulir booking
                                if (liveQuotaAlert) liveQuotaAlert.classList.add('hidden');
                                window.location.href = targetUrl.toString();
                            })
                            .catch(() => {
                                // Fallback jika offline / fetch gagal
                                if (btnBookingText) btnBookingText.textContent =
                                    'Lanjut ke Formulir Booking';
                                window.location.href = targetUrl.toString();
                            });
                    } else {
                        if (btnBookingText) btnBookingText.textContent = 'Lanjut ke Formulir Booking';
                        window.location.href = targetUrl.toString();
                    }
                });
            }

            function openBookingModal(tripInfo) {
                const modalSummaryDate = document.getElementById('modalSummaryDate');
                const modalSummaryPax = document.getElementById('modalSummaryPax');
                const modalSummaryArmada = document.getElementById('modalSummaryArmada');
                const modalSummaryDP = document.getElementById('modalSummaryDP');
                const inputArmada = document.getElementById('inputArmada');

                if (modalSummaryDate) modalSummaryDate.textContent = tripInfo.formattedDate || 'Tanggal Terpilih';
                if (modalSummaryPax) modalSummaryPax.textContent = paxInput.value + ' Pax';
                if (modalSummaryArmada) modalSummaryArmada.textContent = inputArmada ? inputArmada.value :
                    'Innova Reborn';
                if (modalSummaryDP) modalSummaryDP.textContent = dpDisplay.textContent;

                if (bookingModal) {
                    bookingModal.classList.remove('hidden');
                    bookingModal.classList.add('flex');
                }
            }

            function closeBookingModal() {
                if (bookingModal) {
                    bookingModal.classList.add('hidden');
                    bookingModal.classList.remove('flex');
                }
            }

            if (btnCloseModal) btnCloseModal.addEventListener('click', closeBookingModal);
            if (btnCancelModal) btnCancelModal.addEventListener('click', closeBookingModal);

            if (bookingModal) {
                bookingModal.addEventListener('click', function(e) {
                    if (e.target === bookingModal) {
                        closeBookingModal();
                    }
                });
            }

            // Inisialisasi awal saat halaman pertama kali dibuka
            const initialTripInfo = getSelectedTripInfo();
            updateQuotaDisplay(initialTripInfo);
            calculateTotal();
        });
    </script>
</body>

</html>

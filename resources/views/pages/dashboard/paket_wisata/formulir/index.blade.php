{{-- resources/views/pages/dashboard/paket_wisata/formulir/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Formulir Reservasi &amp; DP - D'Java Tour &amp; Travel</title>
    <meta name="description" content="Formulir reservasi paket wisata D'Java Tour & Travel.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'DM Sans', Arial, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-indigo-50/50 text-slate-800 antialiased">
    @include('components.navbar.navbar')

    @php
        $unitPrice = (float) $package->price;
        $downPaymentRate = 30;
        $pax = max(1, (int) old('pax', $selectedPax));
        $remainingSeats = max(0, (int) $selectedTrip->quota - (int) $selectedTrip->booked_seats);

        $totalPrice = $unitPrice * $pax;
        $downPayment = (int) round($totalPrice * $downPaymentRate / 100);
        $remainingPayment = $totalPrice - $downPayment;

        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');

        $departureDate = \Illuminate\Support\Carbon::parse($selectedTrip->departure_date)->translatedFormat('d F Y');

        $vehicleName = old('selected_armada', $selectedArmada);
    @endphp

    <main class="mx-auto w-full max-w-screen-2xl px-4 py-7 sm:px-6 lg:px-8 xl:py-8">
        <header class="mb-8">
            <a href="{{ route('packages.show', $package->slug) }}" class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-teal-700 transition hover:text-teal-900">
                <span aria-hidden="true">&larr;</span>
                Kembali
            </a>

            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        Formulir Reservasi &amp; DP
                    </h1>

                    <p class="mt-2 max-w-4xl text-base leading-7 text-slate-600 sm:text-lg">
                        Lengkapi data perjalanan Anda. Konfirmasi pemesanan akan diproses oleh tim operasional.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-100 px-4 py-3 text-sm font-bold text-slate-700">
                    <svg class="h-4 w-4 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    Data Terenkripsi &amp; Aman
                </div>
            </div>
        </header>

        @if (session('error'))
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">
                <p class="mb-2 font-bold">
                    Periksa kembali data yang Anda masukkan.
                </p>

                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="reservationForm" action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8">
            @csrf

            <input type="hidden" name="package_id" value="{{ $package->id }}">
            <input type="hidden" name="trip_id" value="{{ $selectedTrip->id }}">
            <input type="hidden" name="pax" value="{{ $pax }}">
            <input type="hidden" name="selected_armada" value="{{ $vehicleName }}">

            <div class="space-y-6 lg:col-span-7">
                <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                    <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-slate-900 sm:text-2xl">
                        <svg class="h-5 w-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 11h18"/>
                        </svg>
                        Jadwal &amp; Lokasi Penjemputan
                    </h2>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Tanggal Keberangkatan
                            </label>

                            <div class="flex min-h-12 items-center rounded-xl bg-blue-50 px-4 py-3 text-base text-slate-800">
                                {{ $departureDate }}

                                <svg class="ml-2 h-4 w-4 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Jumlah Peserta (Pax)
                            </label>

                            <div class="flex min-h-12 items-center rounded-xl bg-blue-50 px-4 py-3 text-base text-slate-800">
                                {{ $pax }} orang
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Pilihan Armada
                            </label>

                            <div class="flex min-h-12 items-center rounded-xl bg-blue-50 px-4 py-3 text-base text-slate-800">
                                {{ $vehicleName }}
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="pickup_location" class="mb-2 block text-sm font-semibold text-slate-700">
                                Titik Awal Penjemputan
                            </label>

                            <input id="pickup_location" name="pickup_location" type="text" value="{{ old('pickup_location') }}" maxlength="200" autocomplete="street-address" placeholder="Contoh: Bandara Internasional Juanda (SUB)" required class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="pickup_details" class="mb-2 block text-sm font-semibold text-slate-700">
                                Detail Jam &amp; Alamat Spesifik
                            </label>

                            <textarea id="pickup_details" name="pickup_details" rows="2" maxlength="500" placeholder="Contoh: Flight GA-312 tiba jam 08:30 WIB atau lobi hotel utama" class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100">{{ old('pickup_details') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                    <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-slate-900 sm:text-2xl">
                        <svg class="h-5 w-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                            <circle cx="10" cy="13" r="2"/>
                            <path d="M7 18c.8-1.8 5.2-1.8 6 0"/>
                        </svg>
                        Data Pemesan Resmi
                    </h2>

                    <div class="space-y-5">
                        <div>
                            <label for="customer_name" class="mb-2 block text-sm font-semibold text-slate-700">
                                Nama Lengkap (Sesuai KTP / Paspor)
                            </label>

                            <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', auth()->user()->name ?? '') }}" maxlength="150" autocomplete="name" required placeholder="Masukkan nama penanggung jawab rombongan" class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100">
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="customer_phone" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Nomor WhatsApp Aktif
                                </label>

                                <input id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', auth()->user()->whatsapp ?? '') }}" maxlength="50" autocomplete="tel" inputmode="tel" required placeholder="+62 812-XXXX-XXXX" class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100">
                            </div>

                            <div>
                                <label for="customer_email" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Alamat Email (E-Tiket &amp; Invoice)
                                </label>

                                <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()->email ?? '') }}" maxlength="150" autocomplete="email" required placeholder="nama@domain.com" class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100">
                            </div>
                        </div>

                        <div>
                            <label for="identity_file" class="mb-2 block text-sm font-semibold text-slate-700">
                                Unggah Identitas Penanggung Jawab (Opsional)
                            </label>

                            <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50 p-6 text-center sm:p-8">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-teal-700">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 16V8"/>
                                        <path d="m8 12 4-4 4 4"/>
                                        <path d="M20 16.5a4.5 4.5 0 0 0-1.8-8.6A6.5 6.5 0 0 0 5.5 9.5 3.5 3.5 0 0 0 6 16.5h2"/>
                                    </svg>
                                </span>

                                <label for="identity_file" class="mt-3 block cursor-pointer font-bold text-slate-900">
                                    Klik atau Seret Foto KTP / Paspor
                                </label>

                                <div class="mt-4 flex justify-center">
                                    <input id="identity_file" name="identity_file" type="file" accept="image/jpeg,image/png,application/pdf" class="block w-fit max-w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:font-semibold file:text-teal-800 hover:file:bg-blue-200">
                                </div>

                                <p class="mt-3 text-xs leading-5 text-slate-600">
                                    JPG, PNG, atau PDF, maksimal 2 MB. File disimpan di penyimpanan privat.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">
                                Catatan Khusus / Permintaan Tambahan
                            </label>

                            <textarea id="notes" name="notes" rows="3" maxlength="500" placeholder="Contoh: Bawa kursi bayi, preferensi driver berbahasa Inggris, atau rute tambahan Malang Batu." class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="lg:col-span-5">
                <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7 lg:sticky lg:top-6 lg:p-8">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-teal-700">
                                Ringkasan Biaya
                            </p>

                            <h2 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">
                                Detail Booking Anda
                            </h2>
                        </div>

                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-2 text-xs font-bold text-teal-800">
                            {{ $package->duration }}
                        </span>
                    </div>

                    <div class="rounded-2xl bg-blue-50 p-4 sm:p-5">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900">
                                    {{ $package->name }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $pax }} peserta · {{ $departureDate }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    Armada: {{ $vehicleName }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-xs font-semibold text-slate-500">
                                    Total Paket
                                </p>

                                <p class="text-lg font-extrabold text-slate-900 sm:text-2xl">
                                    {{ $formatRupiah($totalPrice) }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-teal-700">
                                        Uang Muka (DP 30%)
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Dibayar sesuai instruksi pembayaran
                                    </p>
                                </div>

                                <p class="text-base font-extrabold text-teal-700 sm:text-xl">
                                    {{ $formatRupiah($downPayment) }}
                                </p>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-700">
                                        Sisa Pelunasan (70%)
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Dibayarkan sesuai ketentuan paket
                                    </p>
                                </div>

                                <p class="text-base font-extrabold text-slate-900 sm:text-xl">
                                    {{ $formatRupiah($remainingPayment) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Komponen Metode Pembayaran dihilangkan sesuai permintaan. --}}

                    <p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                        Pemesanan belum dianggap lunas sebelum pembayaran DP diverifikasi. Jangan mengirimkan pembayaran ke rekening yang belum diverifikasi oleh pihak D'Java Tour &amp; Travel.
                    </p>

                    <button type="submit" class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-5 py-4 text-base font-bold text-white shadow-sm transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Booking Sekarang
                        <span aria-hidden="true">&rarr;</span>
                    </button>

                    <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs font-semibold text-slate-600">
                        <div>
                            <span class="mb-2 flex justify-center text-teal-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                                    <path d="M16 2v4M8 2v4M3 10h18"/>
                                    <path d="m9 15 2 2 4-4"/>
                                </svg>
                            </span>
                            Reschedule H-3
                        </div>

                        <div>
                            <span class="mb-2 flex justify-center text-teal-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m13 2-3 8h7l-6 12 2-9H6l7-11Z"/>
                                </svg>
                            </span>
                            Konfirmasi Cepat
                        </div>

                        <div>
                            <span class="mb-2 flex justify-center text-teal-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3 14.5 5l3.2-.1.8 3.1 2.5 2-1.2 3 1.2 3-2.5 2-.8 3.1-3.2-.1L12 23l-2.5-2-3.2.1-.8-3.1-2.5-2 1.2-3-1.2-3 2.5-2 .8-3.1 3.2.1L12 3Z"/>
                                    <path d="m8.5 12 2.2 2.2 4.8-4.8"/>
                                </svg>
                            </span>
                            Armada Terverifikasi
                        </div>
                    </div>
                </section>
            </div>
        </form>
    </main>

    @includeIf('components.footer.footer')
</body>
</html>

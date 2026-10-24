{{-- resources/views/components/pembayaran/instruksi-pembayaran.blade.php --}}

@php
    /*
    |----------------------------------------------------------
    | Data tampilan pembayaran
    |----------------------------------------------------------
    | Sesuaikan nama properti dengan data yang dikirim controller.
    | Variabel $booking, $transaction, dan $payment sebaiknya
    | mengikuti struktur project yang sebenarnya.
    */

    $bookingCode = $booking->booking_code
        ?? $bookingCode
        ?? '-';

    $paymentMethod = $transaction->payment_method
        ?? $payment->payment_method
        ?? $paymentMethod
        ?? '-';

    $paymentStatus = strtoupper(
        $transaction->status
        ?? $payment->status
        ?? $paymentStatus
        ?? 'PENDING'
    );

    $paymentAmount = $transaction->amount
        ?? $payment->amount
        ?? $paymentAmount
        ?? 0;

    /*
    | Batas waktu harus diberikan backend.
    | Jika belum tersedia, gunakan null agar tidak menampilkan
    | waktu kedaluwarsa palsu.
    */
    $expiresAt = $transaction->expires_at
        ?? $payment->expires_at
        ?? $expiresAt
        ?? null;

    $expiresAtTimestamp = $expiresAt
        ? \Illuminate\Support\Carbon::parse($expiresAt)->timestamp * 1000
        : null;

    /*
    | URL pembayaran dari backend.
    | Sesuaikan jika project menggunakan nama properti lain.
    */
    $paymentUrl = $transaction->payment_url
        ?? $payment->payment_url
        ?? $paymentUrl
        ?? null;

    $isPaid = in_array($paymentStatus, [
        'PAID',
        'SETTLED',
        'SUCCESS',
        'COMPLETED',
    ], true);

    $isExpired = in_array($paymentStatus, [
        'EXPIRED',
        'CANCELLED',
        'CANCELED',
    ], true);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Instruksi Pembayaran | D'Java Tour &amp; Travel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- NAVBAR --}}
    <header class="border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 lg:px-8">

            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-xl font-bold text-teal-700">
                    ✦
                </span>

                <span>
                    <span class="block text-base font-bold text-slate-900">
                        D'Java Tour &amp; Travel
                    </span>

                    <span class="block text-xs font-semibold uppercase tracking-wide text-teal-700">
                        Explore East Java
                    </span>
                </span>
            </a>

            <nav class="flex flex-wrap items-center gap-2 text-sm font-medium">
                <a href="{{ route('home') }}"
                   class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Beranda
                </a>

                <a href="{{ url('/armada') }}"
                   class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Armada
                </a>

                <a href="{{ url('/paket-wisata') }}"
                   class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Paket Wisata
                </a>

                <a href="{{ route('customer.bookings.index') }}"
                   class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Riwayat Transaksi
                </a>
            </nav>
        </div>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:py-12">

        {{-- BREADCRUMB --}}
        <div class="mb-6 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-teal-700">
                Beranda
            </a>

            <span class="mx-2">›</span>

            <a href="{{ route('customer.bookings.index') }}"
               class="hover:text-teal-700">
                Riwayat Transaksi
            </a>

            <span class="mx-2">›</span>

            <span class="font-semibold text-slate-900">
                Instruksi Pembayaran
            </span>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-bold">Terjadi kesalahan:</p>

                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- KARTU PEMBAYARAN --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- HEADER KARTU --}}
            <div class="border-b border-slate-100 px-5 py-6 sm:px-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="mt-3 text-2xl font-extrabold text-slate-900 sm:text-3xl">
                            Instruksi Pembayaran
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            Kode booking:
                            <span class="font-semibold text-slate-800">
                                {{ $bookingCode }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 p-5 sm:p-8">

                {{-- STATUS PEMBAYARAN --}}
                <div class="rounded-xl p-4
                    {{ $isPaid
                        ? 'border border-green-200 bg-green-50'
                        : ($isExpired
                            ? 'border border-red-200 bg-red-50'
                            : 'border border-blue-200 bg-blue-50') }}">

                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 text-xl">
                            @if ($isPaid)
                                ✓
                            @elseif ($isExpired)
                                !
                            @else
                                ◷
                            @endif
                        </span>

                        <div>
                            <p class="font-bold
                                {{ $isPaid
                                    ? 'text-green-800'
                                    : ($isExpired ? 'text-red-800' : 'text-blue-800') }}">
                                @if ($isPaid)
                                    Pembayaran Berhasil
                                @elseif ($isExpired)
                                    Waktu Pembayaran Berakhir
                                @else
                                    Menunggu Pembayaran
                                @endif
                            </p>

                            <p class="mt-1 text-sm leading-6
                                {{ $isPaid
                                    ? 'text-green-700'
                                    : ($isExpired ? 'text-red-700' : 'text-blue-700') }}">
                                @if ($isPaid)
                                    Pembayaran Anda telah dikonfirmasi.
                                @elseif ($isExpired)
                                    Transaksi ini sudah kedaluwarsa. Silakan periksa status booking Anda.
                                @else
                                    Silakan selesaikan pembayaran sebelum batas waktu berakhir.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- NOMINAL --}}
                <div class="rounded-xl bg-slate-50 p-5">
                    <p class="text-sm font-medium text-slate-500">
                        Nominal yang harus dibayar
                    </p>

                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-teal-700 sm:text-4xl">
                        Rp {{ number_format((float) $paymentAmount, 0, ',', '.') }}
                    </p>

                    <div class="mt-5 grid gap-4 border-t border-slate-200 pt-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500">
                                Metode pembayaran
                            </p>

                            <p class="mt-1 font-bold text-slate-900">
                                {{ $paymentMethod }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Status transaksi
                            </p>

                            <p class="mt-1 font-bold
                                {{ $isPaid
                                    ? 'text-green-700'
                                    : ($isExpired ? 'text-red-700' : 'text-amber-700') }}">
                                {{ $isPaid ? 'Berhasil' : ($isExpired ? 'Kedaluwarsa' : 'Menunggu Pembayaran') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- BATAS WAKTU --}}
                @if (!$isPaid && !$isExpired && $expiresAtTimestamp)
                    <div id="countdown-card"
                         class="rounded-xl border border-amber-200 bg-amber-50 p-5">

                        <p class="text-sm font-semibold text-amber-900">
                            Selesaikan pembayaran sebelum
                        </p>

                        <p class="mt-1 text-sm text-amber-800">
                            {{ \Illuminate\Support\Carbon::parse($expiresAt)->timezone(config('app.timezone'))->format('d F Y, H:i:s') }}
                        </p>

                        <div class="mt-4 flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-xl text-amber-700 shadow-sm">
                                ◷
                            </span>

                            <div>
                                <p id="countdown"
                                   class="text-2xl font-extrabold tabular-nums text-amber-900 sm:text-3xl"
                                   aria-live="polite">
                                    Menghitung waktu...
                                </p>

                                <p class="mt-1 text-xs text-amber-800">
                                    Sisa waktu pembayaran
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif (!$isPaid && !$isExpired)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                        <p class="font-bold">Perhatikan batas waktu pembayaran</p>

                        <p class="mt-1">
                            Batas waktu pembayaran belum tersedia. Silakan lanjutkan pembayaran atau periksa kembali status transaksi.
                        </p>
                    </div>
                @endif

                {{-- TOMBOL LANJUTKAN PEMBAYARAN --}}
                @if (!$isPaid && !$isExpired)
                    @if ($paymentUrl)
                        <a href="{{ $paymentUrl }}"
                           id="continue-payment-button"
                           class="flex w-full items-center justify-center rounded-xl bg-green-600 px-5 py-4 text-center font-bold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                            Lanjutkan Pembayaran
                            <span class="ml-2">→</span>
                        </a>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            Instruksi pembayaran belum tersedia. Silakan tekan tombol cek status atau hubungi layanan pelanggan.
                        </div>
                    @endif

                    <p class="text-sm leading-6 text-slate-500">
                        Pastikan nominal dan metode pembayaran sudah sesuai. Setelah pembayaran berhasil, status transaksi akan diperbarui.
                    </p>
                @endif

                {{-- CEK STATUS --}}
                <form method="POST"
                      action="{{ route('pembayaran.cek-status', ['bookingCode' => $bookingCode]) }}">
                    @csrf

                    <input
                        type="hidden"
                        name="transaction_id"
                        value="{{ $transaction->id }}"
                    >

                    <button type="submit"
                            class="w-full rounded-xl border border-teal-600 px-5 py-3.5 font-bold text-teal-700 transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        Cek Status Pembayaran
                    </button>
                </form>

                {{-- GANTI METODE PEMBAYARAN --}}
                @if (!$isPaid)
                    <a href="{{ route('pembayaran.mulai', ['bookingCode' => $bookingCode]) }}?ganti_metode=1"
                       class="flex w-full items-center justify-center rounded-xl border border-slate-200 px-5 py-3.5 text-center font-semibold text-slate-700 transition hover:border-teal-500 hover:bg-teal-50 hover:text-teal-700">
                        <span class="mr-2">←</span>
                        Ganti Metode Pembayaran
                    </a>
                @endif

                <div class="border-t border-slate-100 pt-5 text-center">
                    <a href="{{ route('customer.bookings.index') }}"
                       class="text-sm font-medium text-slate-500 underline underline-offset-4 hover:text-teal-700">
                        Kembali ke Riwayat Transaksi
                    </a>
                </div>
            </div>
        </section>

        {{-- INFORMASI TAMBAHAN --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-bold text-slate-900">
                Informasi Pembayaran
            </h2>

            <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-600 list-inside list-disc">
                <li>Pastikan Anda membayar sesuai nominal yang tertera.</li>
                <li>Simpan bukti pembayaran jika diperlukan.</li>
                <li>Jangan melakukan pembayaran ulang jika transaksi sudah berhasil.</li>
                <li>Jika menemui kendala, hubungi layanan pelanggan.</li>
            </ul>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="mt-10 border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap justify-between gap-3 px-5 py-6 text-sm text-slate-500 lg:px-8">
            <span>© {{ date('Y') }} D'Java Tour &amp; Travel.</span>

            <span>Perjalanan nyaman dimulai dari sini.</span>
        </div>
    </footer>

    {{-- HITUNG MUNDUR --}}
    @if (!$isPaid && !$isExpired && $expiresAtTimestamp)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countdown = document.getElementById('countdown');
                const countdownCard = document.getElementById('countdown-card');
                const paymentButton = document.getElementById('continue-payment-button');

                if (!countdown || !countdownCard) {
                    return;
                }

                const expiresAt = {{ (int) $expiresAtTimestamp }};

                function updateCountdown() {
                    const remaining = expiresAt - Date.now();

                    if (remaining <= 0) {
                        countdown.textContent = '00:00:00';
                        countdownCard.classList.remove('border-amber-200', 'bg-amber-50');
                        countdownCard.classList.add('border-red-200', 'bg-red-50');

                        countdown.classList.remove('text-amber-900');
                        countdown.classList.add('text-red-700');

                        const message = document.createElement('p');
                        message.className = 'mt-3 text-sm font-semibold text-red-700';
                        message.textContent = 'Waktu pembayaran telah berakhir. Silakan cek status transaksi Anda.';

                        if (!countdownCard.querySelector('[data-expired-message]')) {
                            message.dataset.expiredMessage = 'true';
                            countdownCard.appendChild(message);
                        }

                        if (paymentButton) {
                            paymentButton.classList.add('pointer-events-none', 'opacity-50');
                            paymentButton.setAttribute('aria-disabled', 'true');
                        }

                        return;
                    }

                    const totalSeconds = Math.floor(remaining / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    countdown.textContent = [
                        String(hours).padStart(2, '0'),
                        String(minutes).padStart(2, '0'),
                        String(seconds).padStart(2, '0')
                    ].join(':');
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            });
        </script>
    @endif

</body>
</html>

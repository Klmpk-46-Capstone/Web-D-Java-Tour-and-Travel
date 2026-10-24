{{-- resources/views/components/pembayaran/instruksi-pembayaran.blade.php --}}

@php
    /*
    |----------------------------------------------------------
    | Data Tampilan Pembayaran
    |----------------------------------------------------------
    | Menyiapkan variabel utama dari $booking, $transaction, dan $payment
    | dengan fallback default yang aman.
    */

    $bookingCode = $booking->booking_code
        ?? $bookingCode
        ?? '-';

    $paymentMethod = $transaction->payment_method
        ?? $payment->payment_method
        ?? $paymentMethod
        ?? '-';

    $paymentMethodLabels = [];

    foreach (config('payment_methods.groups', []) as $group) {
        foreach (($group['methods'] ?? []) as $code => $label) {
            $paymentMethodLabels[$code] = $label;
        }
    }

    $paymentMethodLabel = $paymentMethodLabels[$paymentMethod]
        ?? ucwords(strtolower(str_replace('_', ' ', $paymentMethod)));

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
    |----------------------------------------------------------
    | Status Pembayaran
    |----------------------------------------------------------
    */
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

    /*
    |----------------------------------------------------------
    | Batas Waktu Pembayaran (Logika Baru)
    |----------------------------------------------------------
    | DANA, OVO, ShopeePay, dan LinkAja menggunakan fallback 24 jam
    | jika aplikasi memang menetapkan masa berlaku tersebut.
    */
    $paymentInstruction = $transaction->payment_instruction ?? [];

    if (is_string($paymentInstruction)) {
        $paymentInstruction = json_decode($paymentInstruction, true) ?? [];
    }

    $createdAt = \Illuminate\Support\Carbon::parse(
        $transaction->created_at ?? now()
    );

    $expiresAt = $transaction->expires_at
        ?? data_get($paymentInstruction, 'expiry_date')
        ?? data_get($paymentInstruction, 'expires_at')
        ?? data_get($paymentInstruction, 'channel_properties.expires_at')
        ?? null;

    $longExpiryMethods = [
        'DANA',
        'OVO',
        'SHOPEEPAY',
        'LINKAJA',
    ];

    if (!$isPaid && !$isExpired) {
        if (in_array($paymentMethod, $longExpiryMethods, true)) {
            $expiresAt = $createdAt->copy()
                ->addHours(24)
                ->toIso8601String();
        } elseif (!$expiresAt && (
            $paymentMethod === 'QRIS'
            || str_ends_with($paymentMethod, '_VIRTUAL_ACCOUNT')
        )) {
            $expiresAt = $createdAt->copy()
                ->addHours(24)
                ->toIso8601String();
        }
    }

    $expiresAtTimestamp = $expiresAt
        ? \Illuminate\Support\Carbon::parse($expiresAt)->timestamp * 1000
        : null;

    /*
    |----------------------------------------------------------
    | Ekstraksi Instruksi Pembayaran (VA, QR, Redirect URL)
    |----------------------------------------------------------
    */
    $paymentActions = $paymentInstruction['actions'] ?? [];

    $qrString = data_get($paymentInstruction, 'qr_string')
        ?? collect($paymentActions)->first(
            fn ($action) =>
                ($action['type'] ?? '') === 'PRESENT_TO_CUSTOMER'
                && ($action['descriptor'] ?? '') === 'QR_STRING'
        )['value'] ?? null;

    $redirectAction = collect($paymentActions)->first(
        fn ($action) =>
            ($action['type'] ?? '') === 'REDIRECT_CUSTOMER'
            && !empty($action['value'])
    );

    $paymentUrl = $transaction->payment_url
        ?? $payment->payment_url
        ?? $redirectAction['value']
        ?? null;

    $presentActions = collect($paymentActions)
        ->filter(function ($action) {
            $descriptor = $action['descriptor'] ?? '';
            $value = trim((string) ($action['value'] ?? ''));

            return ($action['type'] ?? '') === 'PRESENT_TO_CUSTOMER'
                && $descriptor !== 'QR_STRING'
                && $value !== ''
                && !in_array($value, [
                    'test-qr-string',
                    'some-random-qr-string',
                ], true);
        });

    $paymentDetails = $presentActions
        ->map(fn ($action) => [
            'label' => ucwords(strtolower(str_replace(
                '_',
                ' ',
                $action['descriptor'] ?? 'Instruksi Pembayaran'
            ))),
            'value' => $action['value'] ?? '',
        ])
        ->filter(fn ($item) => $item['value'] !== '');
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

    {{-- Navbar --}}
    @include('components.navbar.navbar')

    {{-- KONTEN UTAMA --}}
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:py-12">

        {{-- BREADCRUMB --}}
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-teal-700">
                Beranda
            </a>

            <span class="mx-2">›</span>

            <a href="{{ route('customer.bookings.index') }}" class="hover:text-teal-700">
                Riwayat Transaksi
            </a>

            <span class="mx-2">›</span>

            <span class="font-semibold text-slate-900">
                Instruksi Pembayaran
            </span>
        </nav>

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
                <div class="rounded-xl p-4 {{ $isPaid ? 'border border-green-200 bg-green-50' : ($isExpired ? 'border border-red-200 bg-red-50' : 'border border-blue-200 bg-blue-50') }}">
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
                            <p class="font-bold {{ $isPaid ? 'text-green-800' : ($isExpired ? 'text-red-800' : 'text-blue-800') }}">
                                @if ($isPaid)
                                    Pembayaran Berhasil
                                @elseif ($isExpired)
                                    Waktu Pembayaran Berakhir
                                @else
                                    Menunggu Pembayaran
                                @endif
                            </p>

                            <p class="mt-1 text-sm leading-6 {{ $isPaid ? 'text-green-700' : ($isExpired ? 'text-red-700' : 'text-blue-700') }}">
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
                                {{ $paymentMethodLabel }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Status transaksi
                            </p>

                            <p class="mt-1 font-bold {{ $isPaid ? 'text-green-700' : ($isExpired ? 'text-red-700' : 'text-amber-700') }}">
                                {{ $isPaid ? 'Berhasil' : ($isExpired ? 'Kedaluwarsa' : 'Menunggu Pembayaran') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- QRIS --}}
                @if (
                    $paymentMethod === 'QRIS'
                    && filled($qrString)
                    && !in_array($qrString, ['some-random-qr-string', 'test-qr-string'], true)
                    && !$isPaid
                    && !$isExpired
                )
                    <div class="rounded-xl border border-teal-200 bg-teal-50 p-5 text-center">
                        <h2 class="font-bold text-teal-900">Scan QRIS untuk Membayar</h2>

                        <p class="mt-2 text-sm text-teal-800">
                            Buka aplikasi mobile banking atau e-wallet, lalu pindai kode QR berikut.
                        </p>

                        <div class="mt-4 flex justify-center">
                            <div id="qris-code"
                                 class="rounded-xl bg-white p-3"
                                 data-qr-string="{{ e($qrString) }}">
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-slate-600">
                            Pastikan nama merchant dan nominal pembayaran sesuai sebelum mengonfirmasi.
                        </p>
                    </div>
                @endif

                {{-- DETAIL PEMBAYARAN (VA / KODE RITEL) --}}
                @if ($paymentDetails->isNotEmpty())
                    <div class="space-y-3 rounded-xl border border-teal-200 bg-teal-50 p-5">
                        <h2 class="font-bold text-teal-900">
                            Detail Pembayaran
                        </h2>

                        @foreach ($paymentDetails as $detail)
                            <div>
                                <p class="text-sm text-teal-800">
                                    {{ $detail['label'] }}
                                </p>

                                <p class="mt-1 break-all text-lg font-bold text-teal-950">
                                    {{ $detail['value'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- BATAS WAKTU --}}
                @if (!$isPaid && !$isExpired && $expiresAtTimestamp)
                    <div id="countdown-card"
                         data-expires-at="{{ (int) $expiresAtTimestamp }}"
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
                <form method="POST" action="{{ route('pembayaran.cek-status', ['bookingCode' => $bookingCode]) }}">
                    @csrf

                    <input type="hidden" name="transaction_id" value="{{ $transaction->id ?? '' }}">

                    <button type="submit"
                            class="w-full rounded-xl border border-teal-600 px-5 py-3.5 font-bold text-teal-700 transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        Cek Status Pembayaran
                    </button>
                </form>

                {{-- GANTI METODE PEMBAYARAN --}}
                @if (!$isPaid)
                    <a href="{{ route('pembayaran.mulai', ['bookingCode' => $bookingCode, 'ganti_metode' => 1]) }}"
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

            <ul class="mt-3 list-inside list-disc space-y-2 text-sm leading-6 text-slate-600">
                <li>Pastikan Anda membayar sesuai nominal yang tertera.</li>
                <li>Simpan bukti pembayaran jika diperlukan.</li>
                <li>Jangan melakukan pembayaran ulang jika transaksi sudah berhasil.</li>
                <li>Jika menemui kendala, hubungi layanan pelanggan.</li>
            </ul>
        </div>
    </main>

    {{-- Footer --}}
    @include('components.footer.footer')

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

                const expiresAt = Number(countdownCard.dataset.expiresAt || 0);

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

    {{-- SCRIPT GENERATE QRIS --}}
    @if (
        $paymentMethod === 'QRIS'
        && filled($qrString)
        && !in_array($qrString, ['some-random-qr-string', 'test-qr-string'], true)
        && !$isPaid
        && !$isExpired
    )
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const element = document.getElementById('qris-code');

                if (!element || typeof QRCode === 'undefined') {
                    return;
                }

                const qrString = element.dataset.qrString;

                if (!qrString || ['some-random-qr-string', 'test-qr-string'].includes(qrString)) {
                    element.textContent = 'Kode QRIS asli belum tersedia.';
                    return;
                }

                new QRCode(element, {
                    text: qrString,
                    width: 240,
                    height: 240,
                    correctLevel: QRCode.CorrectLevel.M
                });
            });
        </script>
    @endif

</body>
</html>

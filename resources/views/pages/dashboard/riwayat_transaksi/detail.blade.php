<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Detail Pemesanan - D'Java Tour &amp; Travel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
{{-- Navbar --}}
@include('components.navbar.navbar')

@php
    $package = $booking->package;
    $trip = $booking->trip;
    $refund = $booking->refund;

    $bookingTitle = $package?->name
        ?? $booking->selected_armada
        ?? 'Reservasi D’Java Tour';

    $imageUrl = $package?->image_url;

    $imageIsSafe = is_string($imageUrl)
        && in_array(parse_url($imageUrl, PHP_URL_SCHEME), ['https', 'http'], true);

    $departureDate = $trip?->departure_date;
    $returnDate = $trip?->return_date;

    $dateText = $departureDate
        ? \Illuminate\Support\Carbon::parse($departureDate)->translatedFormat('d M Y')
        : $booking->created_at?->translatedFormat('d M Y');

    if ($returnDate) {
        $dateText .= ' – ' . \Illuminate\Support\Carbon::parse($returnDate)->translatedFormat('d M Y');
    }

    $transactions = $booking->transactions;

    $paidStatuses = [
        'success',
        'paid',
        'settled',
        'settlement',
        'completed',
        'capture',
    ];

    $paidTransactions = $transactions->filter(
        fn ($transaction) => in_array(
            strtolower((string) $transaction->status),
            $paidStatuses,
            true
        )
    );

    $dpPaid = $paidTransactions->contains(
        fn ($transaction) => $transaction->payment_stage === 'dp'
    );

    $pelunasanPaid = $paidTransactions->contains(
        fn ($transaction) => $transaction->payment_stage === 'pelunasan'
    );

    $isPaid = strtolower((string) $booking->payment_status) === 'paid'
        || ($dpPaid && $pelunasanPaid);

    $isCancelled = in_array(
        strtolower((string) $booking->booking_status),
        ['cancelled', 'canceled'],
        true
    );

    $isRefundRequested = strtolower(
        (string) $booking->booking_status
    ) === 'refund_requested';

    $isCompleted = in_array(
        strtolower((string) $booking->booking_status),
        ['completed', 'finished'],
        true
    );

    $hasSuccessfulPayment = $booking->transactions->contains(function ($transaction) {
        return in_array(
            strtolower((string) $transaction->status),
            ['paid', 'success', 'succeeded', 'settled', 'settlement', 'completed', 'capture'],
            true
        );
    });

    $statusLabel = match (true) {
        $isCancelled => 'Dibatalkan',
        $isRefundRequested => 'Menunggu Refund',
        $isPaid => 'Lunas',
        $dpPaid => 'DP Dibayar',
        $isCompleted => 'Selesai',
        default => 'Menunggu Pembayaran',
    };

    $statusClass = match (true) {
        $isCancelled, $isRefundRequested => 'bg-rose-100 text-rose-800',
        $isPaid => 'bg-blue-100 text-blue-800',
        $dpPaid => 'bg-teal-100 text-teal-800',
        default => 'bg-amber-100 text-amber-900',
    };

    $issuedAt = $booking->created_at?->translatedFormat('d M Y, H:i') ?? '-';

    $dueAt = $booking->created_at
        ? $booking->created_at->copy()->addDay()->translatedFormat('d M Y, H:i')
        : '-';

    $paymentMethodTransaction = $paidTransactions->sortByDesc('id')->first()
        ?? $transactions->whereNotIn('status', ['cancelled', 'canceled'])->sortByDesc('id')->first(
            fn ($transaction) => filled($transaction->payment_channel)
                || filled($transaction->payment_method)
        )
        ?? $transactions->first();

    $channelLabels = [
        'BCA_VIRTUAL_ACCOUNT' => 'BCA Virtual Account',
        'BNI_VIRTUAL_ACCOUNT' => 'BNI Virtual Account',
        'BRI_VIRTUAL_ACCOUNT' => 'BRI Virtual Account',
        'MANDIRI_VIRTUAL_ACCOUNT' => 'Mandiri Virtual Account',
        'PERMATA_VIRTUAL_ACCOUNT' => 'Permata Virtual Account',
        'BCA' => 'BCA Virtual Account',
        'BNI' => 'BNI Virtual Account',
        'BRI' => 'BRI Virtual Account',
        'MANDIRI' => 'Mandiri Virtual Account',
        'PERMATA' => 'Permata Virtual Account',
        'QRIS' => 'QRIS',
        'DANA' => 'DANA',
        'OVO' => 'OVO',
        'SHOPEEPAY' => 'ShopeePay',
        'CARDS' => 'Kartu Kredit / Debit',
        'ALFAMART' => 'Alfamart',
        'INDOMARET' => 'Indomaret',
    ];

    $rawPaymentMethod = $paymentMethodTransaction?->payment_channel
        ?: $paymentMethodTransaction?->payment_method
        ?: $booking->payment_method
        ?: 'Belum dipilih';

    $paymentMethod = $channelLabels[strtoupper($rawPaymentMethod)] ?? $rawPaymentMethod;
@endphp

<div class="min-h-screen bg-slate-50 text-slate-800">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- Breadcrumb dan Aksi --}}
        <div class="mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-600" aria-label="Breadcrumb">
                <a href="{{ route('customer.bookings.index') }}" class="hover:text-teal-700">
                    Riwayat Transaksi
                </a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-900">Detail Invoice</span>
            </nav>

            <div class="flex flex-wrap gap-2">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-100">
                    Cetak
                </button>

                <button type="button"
                        onclick="window.print()"
                        class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-100">
                    Unduh / Simpan PDF
                </button>

                <button type="button"
                        onclick="navigator.clipboard.writeText(window.location.href)"
                        class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-100">
                    Salin Tautan
                </button>
            </div>
        </div>

        {{-- Invoice --}}
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <header class="flex flex-col justify-between gap-6 border-b border-slate-200 p-5 sm:p-7 lg:flex-row lg:items-center">
                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-3">
                        <h1 class="break-words text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                            Invoice #{{ e($booking->booking_code) }}
                        </h1>

                        <span class="rounded-md border border-slate-200 bg-slate-50 px-3 py-1 font-mono text-sm text-slate-700">
                            {{ e($booking->booking_code) }}
                        </span>

                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p class="text-sm leading-6 text-slate-600 sm:text-base">
                        Dibuat {{ $issuedAt }}
                        <span aria-hidden="true">•</span>
                        {{ e($booking->customer_name) }}
                    </p>
                </div>

                <div class="shrink-0 lg:text-right">
                    <p class="text-sm font-medium text-slate-600">
                        Total Kewajiban Pembayaran
                    </p>

                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-teal-800 sm:text-4xl">
                        Rp {{ number_format($totalPrice, 0, ',', '.') }}
                    </p>

                    @if ($canPay)
                        <p class="mt-1 text-sm font-semibold text-amber-800">
                            {{ $dpPaid ? 'Sisa pelunasan' : 'Wajib DP 30%' }}:
                            Rp {{ number_format($billAmount, 0, ',', '.') }}
                        </p>
                    @else
                        <p class="mt-1 text-sm font-semibold text-teal-700">
                            {{ $isPaid ? 'Pembayaran sudah lunas' : 'Tidak ada pembayaran yang dapat dilakukan' }}
                        </p>
                    @endif
                </div>
            </header>

            <div class="grid grid-cols-1 gap-6 p-5 sm:p-7 lg:grid-cols-3">

                {{-- Kolom Utama --}}
                <div class="space-y-5 lg:col-span-2">

                    {{-- Informasi Paket --}}
                    <section class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <div class="h-40 w-full shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:h-32 sm:w-44">
                                @if ($imageIsSafe)
                                    <img src="{{ e($imageUrl) }}"
                                         alt="{{ e($bookingTitle) }}"
                                         loading="lazy"
                                         referrerpolicy="no-referrer"
                                         class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-teal-700">
                                        <svg class="h-12 w-12"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.5"
                                             aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                            <circle cx="8.5" cy="9" r="1.5"></circle>
                                            <path d="m21 15-5-5L5 20"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">
                                        Paket Wisata
                                    </span>

                                    @if ($package?->badge)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ e($package->badge) }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-xl font-bold leading-snug text-slate-900 sm:text-2xl">
                                    {{ e($bookingTitle) }}
                                </h2>

                                @if ($booking->selected_armada)
                                    <p class="mt-1 text-sm text-slate-600">
                                        Armada: {{ e($booking->selected_armada) }}
                                    </p>
                                @endif

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ e($package?->description ?? 'Informasi paket wisata sesuai reservasi Anda.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 border-t border-slate-200 pt-5 sm:grid-cols-3">
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Jadwal Pelaksanaan
                                </p>
                                <p class="mt-2 text-base font-bold text-slate-900">
                                    {{ $dateText ?: 'Belum ditentukan' }}
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ e($package?->duration ?? 'Durasi mengikuti paket') }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Titik Penjemputan
                                </p>
                                <p class="mt-2 break-words text-base font-bold text-slate-900">
                                    {{ e($booking->pickup_location ?: 'Belum ditentukan') }}
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ e($booking->pickup_details ?: 'Tidak ada detail tambahan') }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                    Data Pemesan
                                </p>
                                <p class="mt-2 break-words text-base font-bold text-slate-900">
                                    {{ e($booking->customer_name) }}
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ e($booking->pax) }} peserta
                                </p>
                                <p class="mt-1 break-all text-sm text-slate-600">
                                    {{ e($booking->customer_phone) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Ringkasan Pembayaran --}}
                    <section class="rounded-xl border border-slate-200 p-4 sm:p-5">
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-xl font-bold text-slate-900">
                                Rincian Tagihan & Status Pembayaran
                            </h2>
                            <span class="text-sm text-slate-500">Mata uang: IDR (Rp)</span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-200 bg-blue-50 p-4">
                                <p class="text-sm font-medium text-slate-600">Total Nilai Paket</p>
                                <p class="mt-2 text-xl font-extrabold text-slate-900">
                                    Rp {{ number_format($totalPrice, 0, ',', '.') }}
                                </p>
                                <p class="mt-2 text-sm text-slate-600">
                                    {{ e($booking->pax) }} peserta
                                </p>
                            </div>

                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-sm font-medium text-amber-900">Uang Muka DP (30%)</p>
                                <p class="mt-2 text-xl font-extrabold text-amber-900">
                                    Rp {{ number_format($dpAmount, 0, ',', '.') }}
                                </p>
                                <p class="mt-2 text-sm font-medium text-amber-800">
                                    {{ $dpPaid ? 'Status: Sudah Dibayar' : 'Status: Belum Dibayar' }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-blue-50 p-4">
                                <p class="text-sm font-medium text-slate-600">Sisa Pelunasan</p>
                                <p class="mt-2 text-xl font-extrabold text-slate-900">
                                    Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                                </p>
                                <p class="mt-2 text-sm text-slate-600">
                                    {{ $isPaid ? 'Sudah lunas' : 'Menunggu pembayaran' }}
                                </p>
                            </div>
                        </div>

                        {{-- Metode Pembayaran Card --}}
                        @if ($hasSuccessfulPayment)
                            <div class="mt-4 flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-600">
                                        Metode Pembayaran
                                    </p>

                                    <p class="mt-1 text-base font-bold text-slate-900">
                                        {{ $paymentMethod }}
                                    </p>
                                </div>

                                <span class="inline-flex w-fit items-center rounded-full bg-teal-100 px-3 py-1 text-sm font-semibold text-teal-800">
                                    {{ $isPaid ? 'Pembayaran Lunas' : ($dpPaid ? 'Menunggu Pelunasan' : 'Menunggu DP') }}
                                </span>
                            </div>
                        @endif

                        <div class="mt-5 border-t border-slate-200 pt-4">
                            <h3 class="mb-3 font-bold text-slate-900">Riwayat Transaksi</h3>

                            @forelse ($paidTransactions as $transaction)
                                @php
                                    $transactionStageLabel = match (true) {
                                        $transaction->payment_stage === 'dp' => 'DP',
                                        $transaction->payment_stage === 'pelunasan' => 'Pelunasan',
                                        default => ucfirst((string) ($transaction->payment_stage ?: 'Pembayaran')),
                                    };
                                @endphp
                                <div class="flex flex-col justify-between gap-2 border-b border-slate-100 py-3 last:border-0 sm:flex-row sm:items-center">
                                    <div>
                                        <p class="break-all font-semibold text-slate-900">
                                            {{ e($transaction->transaction_code) }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $transactionStageLabel }}
                                            <span aria-hidden="true">•</span>
                                            {{ $transaction->paid_at?->translatedFormat('d M Y, H:i') ?? $transaction->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                        </p>
                                    </div>

                                    <div class="sm:text-right">
                                        <p class="font-bold text-slate-900">
                                            Rp {{ number_format((int) $transaction->amount, 0, ',', '.') }}
                                        </p>
                                        <p class="mt-1 text-sm font-semibold text-teal-700">
                                            Paid
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-600">
                                    Belum ada transaksi pembayaran yang berhasil.
                                </p>
                            @endforelse
                        </div>
                    </section>

                    {{-- Tagihan dan Pembayaran / Rating --}}
                    @if ($canPay)
                        <section class="rounded-xl border border-teal-700 bg-white p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-800">
                                    <svg class="h-6 w-6"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.7"
                                         aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                        <path d="M3 9h18M7 14h4"></path>
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h2 class="text-xl font-bold text-slate-900">
                                        Bayar {{ $dpPaid ? 'Pelunasan' : 'Uang Muka (DP)' }}
                                    </h2>

                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        {{ $dpPaid ? 'Sisa pembayaran' : 'Pembayaran awal 30%' }}:
                                        <span class="font-bold text-teal-800">
                                            Rp {{ number_format($billAmount, 0, ',', '.') }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-bold text-slate-900">Pembayaran Online</p>
                                    <p class="mt-1 text-sm leading-5 text-slate-600">
                                        Pilih metode pembayaran yang tersedia pada halaman pembayaran Xendit.
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="font-bold text-slate-900">Konfirmasi Pembayaran</p>
                                    <p class="mt-1 text-sm leading-5 text-slate-600">
                                        Status akan diperbarui setelah pembayaran berhasil diverifikasi.
                                    </p>
                                </div>
                            </div>

                            <a href="{{ route('pembayaran.mulai', ['bookingCode' => $booking->booking_code]) }}"
                               class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-teal-700 px-5 py-3 text-base font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                                Bayar {{ $dpPaid ? 'Pelunasan' : 'DP' }} Sekarang
                                (Rp {{ number_format($billAmount, 0, ',', '.') }})
                            </a>

                            <p class="mt-3 text-center text-sm text-slate-500">
                                Nominal dan status pembayaran ditentukan oleh server.
                            </p>
                        </section>
                    @elseif ($isPaid && !$isCancelled && !$isRefundRequested)
                        <section class="rounded-xl border border-teal-200 bg-teal-50 p-5 sm:p-6">
                            <h2 class="text-xl font-bold text-slate-900">
                                Terima kasih telah memilih D’Java Tour!
                            </h2>

                            <p class="mt-2 text-base leading-7 text-slate-600">
                                Pembayaran kamu sudah lunas. Bagikan pengalamanmu
                                dengan memberikan rating pada pemesanan ini.
                            </p>

                            @if ($hasReviewed)
                                <p class="mt-4 rounded-lg bg-white p-4 font-semibold text-teal-800">
                                    Rating untuk pemesanan ini sudah dikirim. Terima kasih!
                                </p>
                            @else
                                <a
                                    href="{{ route('customer.bookings.rating', [
                                        'bookingCode' => $booking->booking_code,
                                    ]) }}"
                                    class="mt-5 inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-amber-500 px-6 py-3 text-base font-bold text-white transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                >
                                    <span aria-hidden="true">★</span>
                                    Tulis Rating
                                </a>
                            @endif
                        </section>
                    @endif
                </div>

                {{-- Kolom Samping --}}
                <aside class="space-y-5">
                    <section class="rounded-xl border border-slate-200 bg-white p-5">
                        <h2 class="text-lg font-bold text-slate-900">
                            Jaminan Layanan & Keamanan
                        </h2>

                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                            <li class="flex gap-2">
                                <span class="font-bold text-teal-700" aria-hidden="true">✓</span>
                                Informasi reservasi tercatat berdasarkan data pemesanan.
                            </li>
                            <li class="flex gap-2">
                                <span class="font-bold text-teal-700" aria-hidden="true">✓</span>
                                Status pembayaran diperiksa melalui transaksi yang tersimpan dan verifikasi penyedia pembayaran.
                            </li>
                            <li class="flex gap-2">
                                <span class="font-bold text-teal-700" aria-hidden="true">✓</span>
                                Detail booking hanya dapat diakses oleh pemilik pemesanan yang berwenang.
                            </li>
                        </ul>
                    </section>

                    {{-- Pembatalan & Refund Section --}}
                    <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                        <h2 class="text-lg font-bold text-slate-900">
                            Pembatalan & Refund
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            @if ($isCancelled)
                                Booking ini sudah dibatalkan.
                            @elseif ($isRefundRequested || $refund)
                                Pengajuan refund sudah tercatat. Status dan proses
                                selanjutnya dapat diperiksa melalui halaman refund.
                            @elseif ($hasSuccessfulPayment)
                                Karena pembayaran sudah dilakukan, pembatalan akan
                                diproses sebagai pengajuan refund. Persetujuan dan
                                nominal pengembalian dana mengikuti kebijakan D’Java Tour.
                            @else
                                Booking ini belum memiliki pembayaran yang berhasil.
                                Kamu dapat membatalkan booking sebelum melakukan pembayaran.
                            @endif
                        </p>

                        @if ($refund)
                            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                <p class="text-sm font-semibold text-amber-900">
                                    Status Refund
                                </p>

                                <p class="mt-1 text-sm text-amber-800">
                                    {{ ucfirst(str_replace('_', ' ', (string) $refund->status)) }}
                                </p>
                            </div>
                        @elseif (!$isCancelled && !$isRefundRequested && !$isCompleted)
                            @if ($hasSuccessfulPayment)
                                <a
                                    href="{{ route('customer.bookings.refund-page', ['bookingCode' => $booking->booking_code]) }}"
                                    class="mt-4 inline-flex h-11 items-center justify-center rounded-lg border border-rose-200 bg-white px-5 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                >
                                    Ajukan Refund
                                </a>
                            @else
                                <a
                                    href="{{ route('customer.bookings.cancel-page', ['bookingCode' => $booking->booking_code]) }}"
                                    class="mt-4 inline-flex h-11 items-center justify-center rounded-lg border border-rose-200 bg-white px-5 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                >
                                    Batal Booking
                                </a>
                            @endif
                        @endif
                    </section>

                    <a href="{{ route('customer.bookings.index') }}"
                       class="inline-flex h-12 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 hover:bg-slate-100">
                        Kembali ke Riwayat Transaksi
                    </a>
                </aside>
            </div>

            <footer class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-7">
                <p class="text-sm text-slate-500">
                    Invoice ini merupakan ringkasan informasi pemesanan dan pembayaran yang tersimpan di sistem.
                </p>
            </footer>
        </article>
    </div>
</div>
{{-- Footer --}}
@include('components.footer.footer')
</body>
</html>

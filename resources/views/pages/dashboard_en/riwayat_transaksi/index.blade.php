<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Transaction History - D'Java Tour &amp; Travel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'DM Sans', Arial, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">

    {{-- Navbar --}}
    @include('components.navbar.navbar')

    {{-- Konten utama --}}
    <main class="min-h-screen bg-slate-50">
        <section class="mx-auto w-full max-w-screen-2xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Header halaman --}}
            <header class="flex flex-col gap-3 border-b border-blue-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                        Transaction History
                    </h1>

                    <p class="mt-1 text-base leading-7 text-slate-600 sm:text-lg">
                        Manage your car rental bookings and tour package orders.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-100 px-4 py-2 text-sm font-semibold text-slate-800">
                    <span class="h-2 w-2 rounded-full bg-teal-700"></span>

                    {{ number_format($counts['all'] ?? 0, 0, '.', ',') }}
                    Total Reservations
                </div>
            </header>

            {{-- Notifikasi berhasil --}}
            @if (session('success'))
                <div
                    role="status"
                    class="mt-5 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Notifikasi peringatan --}}
            @if (session('warning'))
                <div
                    role="alert"
                    class="mt-5 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-medium text-orange-900"
                >
                    {{ session('warning') }}
                </div>
            @endif

            {{-- Validasi form --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                >
                    <p class="font-bold">
                        Please check the data you entered.
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- H-1 Customer Notification Banner --}}
            @php
                $h1CustomerNotifs = \App\Services\CustomerNotificationService::forUser(auth()->user())
                    ->whereIn('type', ['h1_reminder', 'today_trip']);
            @endphp

            @if ($h1CustomerNotifs->isNotEmpty())
                <div class="mt-6 space-y-3">
                    @foreach ($h1CustomerNotifs as $hn)
                        <div class="rounded-2xl border border-amber-300 bg-gradient-to-r from-amber-50 via-amber-100/50 to-white p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <span class="relative flex h-10 w-10 shrink-0 place-items-center">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-50"></span>
                                    <span class="relative grid h-10 w-10 place-items-center rounded-xl bg-amber-500 text-white shadow-xs">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                                            🔔 {{ $hn['title'] }}
                                        </span>
                                        <span class="text-xs text-amber-800 font-semibold">{{ $hn['date'] }}</span>
                                    </div>
                                    <p class="mt-1 text-sm font-semibold text-slate-800 leading-relaxed">
                                        {{ $hn['message'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                <a
                                    href="{{ $hn['url'] }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-amber-700 transition no-underline w-full sm:w-auto"
                                >
                                    <span>Details & Plate</span>
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Filter status dan pencarian --}}
            <div class="mt-6">
                @include('pages.dashboard_en.riwayat_transaksi.components.filters')
            </div>

            {{-- Daftar transaksi --}}
            <div class="mt-6 space-y-4">
                @forelse ($bookings as $booking)
                    @include('pages.dashboard_en.riwayat_transaksi.components.booking-card', [
                        'booking' => $booking,
                    ])
                @empty
                    @include('pages.dashboard_en.riwayat_transaksi.components.empty-state')
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($bookings->hasPages())
                <nav
                    class="mt-7 flex flex-col gap-4 rounded-2xl border border-blue-100 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
                    aria-label="Transaction history pagination"
                >
                    <p class="text-sm text-slate-600">
                        Showing

                        <span class="font-semibold text-slate-900">
                            {{ $bookings->firstItem() }}
                        </span>

                        to

                        <span class="font-semibold text-slate-900">
                            {{ $bookings->lastItem() }}
                        </span>

                        of

                        <span class="font-semibold text-slate-900">
                            {{ $bookings->total() }}
                        </span>

                        transactions
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Previous page --}}
                        @if ($bookings->onFirstPage())
                            <span class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-400">
                                Previous
                            </span>
                        @else
                            <a
                                href="{{ $bookings->previousPageUrl() }}"
                                class="rounded-lg border border-blue-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50"
                            >
                                Previous
                            </a>
                        @endif

                        {{-- Page number --}}
                        <span class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white">
                            {{ $bookings->currentPage() }}
                            /
                            {{ $bookings->lastPage() }}
                        </span>

                        {{-- Next page --}}
                        @if ($bookings->hasMorePages())
                            <a
                                href="{{ $bookings->nextPageUrl() }}"
                                class="rounded-lg border border-blue-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50"
                            >
                                Next
                            </a>
                        @else
                            <span class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-400">
                                Next
                            </span>
                        @endif
                    </div>
                </nav>
            @endif

        </section>
    </main>

    {{-- Footer --}}
    @include('components.footer.footer')

    {{-- Script Pemeriksaan Status Pembayaran Otomatis (Polling) --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const checkPaymentStatus = async (card) => {
            const url = card.dataset.statusUrl;

            if (!url || card.dataset.checking === 'true') {
                return;
            }

            card.dataset.checking = 'true';

            try {
                const headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };

                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: headers
                });

                if (!response.ok) {
                    console.error(
                        'Failed to check payment status:',
                        response.status
                    );
                    return;
                }

                const result = await response.json();

                // Muat ulang halaman hanya satu kali jika status pembayaran berubah (misal dari pending ke partial)
                if (
                    result.payment_status &&
                    result.payment_status !== card.dataset.paymentStatus
                ) {
                    window.location.reload();
                }
            } catch (error) {
                console.error(
                    'An error occurred while checking payment:',
                    error
                );
            } finally {
                card.dataset.checking = 'false';
            }
        };

        const cards = document.querySelectorAll(
            'article[data-status-url][data-payment-status]'
        );

        cards.forEach((card) => {
            const status = card.dataset.paymentStatus;

            // Cek otomatis hanya untuk booking yang belum lunas.
            if (status !== 'paid') {
                checkPaymentStatus(card);

                // Cek ulang setiap 10 detik selama halaman aktif/terbuka.
                window.setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        checkPaymentStatus(card);
                    }
                }, 10000);
            }
        });
    });
    </script>
</body>
</html>

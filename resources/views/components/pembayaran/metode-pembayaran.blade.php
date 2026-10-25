
<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'id' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ app()->getLocale() === 'en' ? 'Payment Method' : 'Metode Pembayaran' }} - D'Java Tour &amp; Travel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white text-slate-800 antialiased">
    @include('components.navbar.navbar')

    @php
        $isEnglish = app()->getLocale() === 'en';
        $paymentGroups = config('payment_methods.groups', []);

        $paymentGroupLogos = [
            'bank' => [
                'BCA' => 'BCA',
                'BNI' => 'BNI',
                'BRI' => 'BRI',
                'MANDIRI' => 'mandiri',
                'PERMATA' => 'PERMATA',
                'BSI' => 'BSI',
                'CIMB' => 'CIMB',
                'SAHABAT_SAMPOERNA' => 'Sampoerna',
                'BNC' => 'BNC',
                'OTHER' => $isEnglish ? 'Others' : 'Lainnya',
            ],
            'qr' => [
                'QRIS' => 'QRIS',
            ],
            'wallet' => [
                'DANA' => 'DANA',
                'OVO' => 'OVO',
                'SHOPEEPAY' => 'ShopeePay',
                'LINKAJA' => 'LinkAja',
                'GOPAY' => 'GoPay',
                'ASTRAPAY' => 'AstraPay',
            ],
            'card' => [
                'CARDS' => $isEnglish ? 'Card' : 'Kartu',
            ],
            'retail' => [
                'ALFAMART' => 'Alfamart',
                'INDOMARET' => 'Indomaret',
            ],
        ];
    @endphp

    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-10">
        @if (session('success'))
            <div
                role="status"
                class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800"
            >
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                role="alert"
                class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
            >
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                role="alert"
                class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
            >
                <p class="font-semibold">{{ $isEnglish ? 'Please check the payment details:' : 'Periksa kembali data pembayaran:' }}</p>

                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <header class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                {{ $isEnglish ? 'Continue Payment' : 'Lanjutkan Pembayaran' }}
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                {{ $isEnglish ? 'Booking:' : 'Booking:' }}
                <span class="font-semibold text-slate-900">
                    {{ $booking->booking_code }}
                </span>
            </p>
        </header>

        {{-- Ringkasan pembayaran --}}
        <section
            aria-label="{{ $isEnglish ? 'Payment summary' : 'Ringkasan pembayaran' }}"
            class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-5"
        >
            <div class="flex items-start justify-between gap-4">
                <span class="text-sm text-slate-600">
                    {{ $isEnglish ? 'Order total' : 'Total pesanan' }}
                </span>

                <span class="text-right font-semibold text-slate-900">
                    Rp {{ $isEnglish ? number_format((int) $booking->total_price, 0, '.', ',') : number_format((int) $booking->total_price, 0, ',', '.') }}
                </span>
            </div>

            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="text-sm font-medium text-slate-600">
                    {{ $stage === 'dp' ? ($isEnglish ? '30% DP Payment' : 'Pembayaran DP 30%') : ($isEnglish ? 'Balance Payment' : 'Pelunasan') }}
                </p>

                <p class="mt-1 text-2xl font-bold tracking-tight text-green-700">
                    Rp {{ $isEnglish ? number_format((int) $amount, 0, '.', ',') : number_format((int) $amount, 0, ',', '.') }}
                </p>

                <p class="mt-1 text-sm leading-6 text-slate-500">
                    {{ $stage === 'dp'
                        ? ($isEnglish ? 'Pay the down payment to continue your reservation.' : 'Bayar uang muka untuk melanjutkan reservasi.')
                        : ($isEnglish ? 'Pay the remaining balance after the DP has been successfully paid.' : 'Bayar sisa tagihan setelah DP berhasil dibayar.') }}
                </p>
            </div>
        </section>

        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-800">
            {{ $isEnglish ? 'Payment Method' : 'Metode Pembayaran' }}
        </h2>

        <form
            action="{{ localized_route('pembayaran.buat', ['bookingCode' => $booking->booking_code]) }}"
            method="POST"
            id="payment-form"
        >
            @csrf

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                @forelse ($paymentGroups as $groupKey => $group)
                    @php
                        $methods = $group['methods'] ?? [];
                        $groupLabel = $isEnglish
                            ? ($group['label_en'] ?? $group['label'] ?? 'Payment Method')
                            : ($group['label'] ?? 'Metode Pembayaran');
                        $normalizedLabel = strtolower($groupLabel);

                        $groupType = match (true) {
                            str_contains($normalizedLabel, 'virtual'),
                            str_contains($normalizedLabel, 'bank'),
                            str_contains($normalizedLabel, 'transfer') => 'bank',

                            str_contains($normalizedLabel, 'qris'),
                            str_contains($normalizedLabel, 'qr') => 'qr',

                            str_contains($normalizedLabel, 'wallet'),
                            str_contains($normalizedLabel, 'ewallet'),
                            str_contains($normalizedLabel, 'e-wallet') => 'wallet',

                            str_contains($normalizedLabel, 'card'),
                            str_contains($normalizedLabel, 'kartu') => 'card',

                            str_contains($normalizedLabel, 'counter'),
                            str_contains($normalizedLabel, 'ritel'),
                            str_contains($normalizedLabel, 'retail') => 'retail',

                            default => 'other',
                        };

                        $methodCodes = array_map(
                            'strtoupper',
                            array_map('strval', array_keys($methods))
                        );

                        $logoCodes = [];

                        foreach ($methodCodes as $methodCode) {
                            if (isset($paymentGroupLogos[$groupType][$methodCode])) {
                                $logoCodes[] = $methodCode;
                            }
                        }

                        if ($groupType === 'other') {
                            foreach ($methodCodes as $methodCode) {
                                foreach ($paymentGroupLogos as $logoGroup) {
                                    if (isset($logoGroup[$methodCode])) {
                                        $logoCodes[] = $methodCode;
                                        break;
                                    }
                                }
                            }
                        }

                        $logoCodes = array_values(array_unique($logoCodes));
                        $visibleLogos = array_slice($logoCodes, 0, 3);
                        $remainingLogos = max(0, count($logoCodes) - count($visibleLogos));

                        $isCardGroup = $groupType === 'card'
                            || in_array('CARDS', $methodCodes, true);
                    @endphp

                    <details class="group border-b border-slate-200 last:border-b-0">
                        <summary
                            class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-teal-600 sm:px-5"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                        {{ $groupType === 'bank' ? 'bg-teal-50 text-teal-700' : '' }}
                                        {{ $groupType === 'qr' ? 'bg-blue-50 text-blue-700' : '' }}
                                        {{ $groupType === 'wallet' ? 'bg-violet-50 text-violet-700' : '' }}
                                        {{ $groupType === 'card' ? 'bg-slate-100 text-slate-700' : '' }}
                                        {{ $groupType === 'retail' ? 'bg-amber-50 text-amber-700' : '' }}
                                        {{ $groupType === 'other' ? 'bg-slate-100 text-slate-700' : '' }}"
                                    aria-hidden="true"
                                >
                                    @if ($groupType === 'bank')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18L12 3 3 9Z M5 10v8m4-8v8m6-8v8m4-8v8M3 20h18M2 22h20" />
                                        </svg>
                                    @elseif ($groupType === 'qr')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM19 14v2m-2 3h4v2h-4zM19 19v-2" />
                                        </svg>
                                    @elseif ($groupType === 'wallet')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h15a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h13" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11h5v5h-5a2.5 2.5 0 0 1 0-5Z" />
                                            <circle cx="17" cy="13.5" r=".6" fill="currentColor" />
                                        </svg>
                                    @elseif ($groupType === 'card')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                            <rect x="2.5" y="5" width="19" height="14" rx="2" />
                                            <path stroke-linecap="round" d="M3 9h18M6 14h5" />
                                        </svg>
                                    @elseif ($groupType === 'retail')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18v11H3zM2 10l2-6h16l2 6M8 21v-6h8v6M7 7h.01M12 7h.01M17 7h.01" />
                                        </svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5">
                                            <rect x="3" y="4" width="18" height="16" rx="2" />
                                            <path stroke-linecap="round" d="M7 9h10M7 13h6" />
                                        </svg>
                                    @endif
                                </span>

                                <span class="text-sm font-semibold text-slate-800 sm:text-base">
                                    {{ $groupLabel }}
                                </span>
                            </span>

                            <span class="flex shrink-0 items-center gap-2">
                                @foreach ($visibleLogos as $logoCode)
                                    @php
                                        $logoLabel = $paymentGroupLogos[$groupType][$logoCode]
                                            ?? $logoCode;

                                        $logoStyle = match ($logoCode) {
                                            'BCA' => 'text-blue-700',
                                            'BNI' => 'text-orange-600',
                                            'BRI' => 'text-blue-600',
                                            'MANDIRI' => 'text-blue-800',
                                            'DANA' => 'text-sky-600',
                                            'OVO' => 'text-violet-700',
                                            'GOPAY' => 'text-sky-700',
                                            'QRIS' => 'text-slate-800',
                                            'ALFAMART' => 'text-red-600',
                                            'INDOMARET' => 'text-blue-700',
                                            default => 'text-slate-700',
                                        };
                                    @endphp

                                    <span
                                        class="inline-flex h-7 min-w-8 items-center justify-center rounded-sm border border-slate-200 bg-white px-1.5 text-center text-xs font-bold {{ $logoStyle }}"
                                        title="{{ $logoLabel }}"
                                        aria-label="{{ $logoLabel }}"
                                    >
                                        {{ $logoLabel }}
                                    </span>
                                @endforeach

                                @if ($remainingLogos > 0)
                                    <span class="inline-flex h-7 min-w-8 items-center justify-center rounded-sm border border-slate-200 bg-white px-1.5 text-xs font-medium text-slate-600">
                                        +{{ $remainingLogos }}
                                    </span>
                                @endif

                                <svg
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    class="ml-1 h-4 w-4 text-slate-500 transition-transform group-open:rotate-180"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5" />
                                </svg>
                            </span>
                        </summary>

                        <div class="border-t border-slate-100 bg-slate-50 p-4 sm:p-5">
                            @if (count($methods) > 0)
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($methods as $code => $label)
                                        @php
                                            $normalizedCode = strtoupper((string) $code);
                                            $isUnsupportedCard = $normalizedCode === 'CARDS';
                                        @endphp

                                        <label
                                            class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-3 transition-colors
                                                {{ $isUnsupportedCard ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:border-teal-500 hover:bg-teal-50/30' }}"
                                        >
                                            <input
                                                type="radio"
                                                name="channel_code"
                                                value="{{ $code }}"
                                                class="h-4 w-4 shrink-0 accent-teal-600 focus:ring-teal-600"
                                                @checked(old('channel_code') === (string) $code)
                                                @disabled($isUnsupportedCard)
                                                required
                                            >

                                            <span class="min-w-0 flex-1 text-sm font-medium text-slate-700">
                                                {{ $label }}
                                            </span>

                                            @if ($isUnsupportedCard)
                                                <span class="text-xs text-slate-500">
                                                    {{ $isEnglish ? 'Not active' : 'Belum aktif' }}
                                                </span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>

                                @if ($isCardGroup)
                                    <p class="mt-3 text-xs leading-5 text-slate-500">
                                        {{ $isEnglish
                                            ? 'Card payments are not enabled in the system yet.'
                                            : 'Pembayaran kartu belum diaktifkan pada sistem saat ini.' }}
                                    </p>
                                @endif
                            @else
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ $isEnglish
                                        ? 'This method is not enabled yet. The channel must be configured in your Xendit account first.'
                                        : 'Metode ini belum diaktifkan. Channel perlu dikonfigurasi di akun Xendit terlebih dahulu.' }}
                                </p>
                            @endif
                        </div>
                    </details>
                @empty
                    <p class="p-5 text-sm leading-6 text-slate-600">
                        {{ $isEnglish
                            ? 'No payment methods are available yet. Please contact customer service.'
                            : 'Belum ada metode pembayaran yang tersedia. Silakan hubungi layanan pelanggan.' }}
                    </p>
                @endforelse
            </div>

            <button
                type="submit"
                class="mt-6 flex w-full items-center justify-center rounded-xl bg-green-600 px-5 py-3.5 font-semibold text-white transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2"
            >
                {{ $stage === 'dp'
                    ? ($isEnglish ? 'Pay 30% DP' : 'Bayar DP 30%')
                    : ($isEnglish ? 'Pay 70% Balance' : 'Bayar Pelunasan 70%') }}
                <span class="ml-2" aria-hidden="true">→</span>
            </button>
        </form>

        <p class="mt-4 text-xs leading-5 text-slate-500">
            {{ $isEnglish
                ? 'Payment will be confirmed after its status is verified through Xendit. Make sure the amount and payment method are correct. Do not share sensitive payment information with unknown parties.'
                : 'Pembayaran akan dikonfirmasi setelah statusnya diverifikasi melalui Xendit. Pastikan nominal dan metode pembayaran sudah sesuai. Jangan membagikan informasi pembayaran sensitif kepada pihak yang tidak dikenal.' }}
        </p>
    </main>

    @includeIf('components.footer.footer')
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Metode Pembayaran - D'Java Tour &amp; Travel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800">
    @include('components.navbar.navbar')

    <main class="mx-auto max-w-3xl px-4 py-10">
        @if (session('success'))
            <div class="mb-5 rounded-xl bg-green-50 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl bg-red-50 p-4 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 p-4 text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <h1 class="mb-2 text-2xl font-bold">Lanjutkan Pembayaran</h1>
        <p class="mb-6 text-sm text-slate-600">
            Booking: <strong>{{ e($booking->booking_code) }}</strong>
        </p>

        <div class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-5">
            <div class="flex justify-between gap-4">
                <span>Total pesanan</span>
                <strong>
                    Rp {{ number_format((int) $booking->total_price, 0, ',', '.') }}
                </strong>
            </div>

            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="text-sm font-semibold text-slate-600">
                    {{ $stage === 'dp' ? 'Pembayaran DP 30%' : 'Pelunasan' }}
                </p>

                <p class="mt-1 text-2xl font-bold text-green-700">
                    Rp {{ number_format($amount, 0, ',', '.') }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $stage === 'dp'
                        ? 'Bayar uang muka untuk melanjutkan reservasi.'
                        : 'Bayar sisa tagihan setelah DP berhasil dibayar.' }}
                </p>
            </div>
        </div>

        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide">
            Metode Pembayaran
        </h2>

        <form
            action="{{ route('pembayaran.buat', ['bookingCode' => $booking->booking_code]) }}"
            method="POST"
            id="payment-form"
        >
            @csrf

            <div class="overflow-hidden rounded-xl border border-slate-200">
                @foreach (config('payment_methods.groups', []) as $groupKey => $group)
                    <details class="group border-b border-slate-200 last:border-b-0">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-5 hover:bg-slate-50">
                            <span class="flex items-center gap-3 font-medium">
                                <span class="text-2xl" aria-hidden="true">
                                    {{ $group['icon'] ?? '💳' }}
                                </span>
                                {{ e($group['label'] ?? '') }}
                            </span>
                            <span class="text-slate-500 transition group-open:rotate-180">
                                ▾
                            </span>
                        </summary>

                        <div class="border-t border-slate-100 bg-slate-50 p-4">
                            @if (count($group['methods'] ?? []) > 0)
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($group['methods'] as $code => $label)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white p-4 hover:border-green-500">
                                            <input
                                                type="radio"
                                                name="channel_code"
                                                value="{{ e($code) }}"
                                                class="h-4 w-4 accent-green-600"
                                                required
                                            >
                                            <span class="text-sm font-medium">
                                                {{ e($label) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-600">
                                    Metode ini belum diaktifkan. Channel perlu
                                    dikonfigurasi di akun Xendit terlebih dahulu.
                                </p>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>

            <button
                type="submit"
                class="mt-6 w-full rounded-xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700"
            >
                {{ $stage === 'dp'
                    ? 'Bayar DP 30%'
                    : 'Bayar Pelunasan 70%' }}
            </button>
        </form>

        <p class="mt-4 text-xs leading-5 text-slate-500">
            Pembayaran akan dikonfirmasi setelah statusnya diverifikasi melalui
            Xendit. Jangan mengirim bukti pembayaran yang berisi informasi
            sensitif kepada pihak yang tidak dikenal.
        </p>
    </main>

    @includeIf('components.footer.footer')
</body>
</html>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran - D'Java Tour &amp; Travel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-indigo-50/50 text-slate-800">
    @include('components.navbar.navbar')

    @php
        $instruction = $transaction->payment_instruction ?? [];
        $actions = $instruction['actions'] ?? [];
        $redirectAction = collect($actions)->firstWhere('type', 'REDIRECT_CUSTOMER');
        $presentAction = collect($actions)->firstWhere('type', 'PRESENT_TO_CUSTOMER');
        $isPaid = in_array($transaction->status, [
            'paid', 'settled', 'completed', 'success'
        ], true);
    @endphp

    <main class="mx-auto max-w-2xl px-4 py-12">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold">Instruksi Pembayaran</h1>

            <p class="mt-2 text-sm text-slate-600">
                Booking {{ $booking->booking_code }}
            </p>

            @if (session('success'))
                <div class="mt-5 rounded-xl bg-green-50 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-5 rounded-xl bg-red-50 p-4 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('status_message'))
                <div class="mt-5 rounded-xl bg-blue-50 p-4 text-blue-800">
                    {{ session('status_message') }}
                </div>
            @endif

            <div class="mt-6 rounded-xl bg-slate-50 p-5">
                <p class="text-sm text-slate-600">
                    {{ $stage === 'dp' ? 'Pembayaran DP 30%' : 'Pelunasan 70%' }}
                </p>

                <p class="mt-1 text-3xl font-bold text-green-700">
                    Rp {{ number_format((int) $transaction->amount, 0, ',', '.') }}
                </p>

                <p class="mt-3 text-sm">
                    Metode:
                    <strong>{{ $transaction->payment_channel }}</strong>
                </p>

                <p class="mt-2 text-sm">
                    Status:
                    <strong>{{ strtoupper($transaction->status) }}</strong>
                </p>

                <p class="mt-2 break-all text-xs text-slate-500">
                    Kode transaksi: {{ $transaction->transaction_code }}
                </p>
            </div>

            @if ($isPaid)
                <div class="mt-6 rounded-xl bg-green-50 p-4 text-green-800">
                    Pembayaran sudah dikonfirmasi.
                </div>
            @else
                @if ($redirectAction && !empty($redirectAction['value']))
                    <a
                        href="{{ $redirectAction['value'] }}"
                        class="mt-6 block rounded-xl bg-green-600 px-5 py-3 text-center font-semibold text-white hover:bg-green-700"
                    >
                        Lanjutkan autentikasi pembayaran
                    </a>
                @endif

                @if ($presentAction && !empty($presentAction['value']))
                    <div class="mt-6 rounded-xl border border-slate-200 p-4">
                        <h2 class="font-semibold">Informasi dari penyedia pembayaran</h2>
                        <p class="mt-2 break-all text-sm">
                            {{ $presentAction['value'] }}
                        </p>
                    </div>
                @endif

                <p class="mt-5 text-sm leading-6 text-slate-600">
                    Ikuti instruksi yang diberikan untuk menyelesaikan pembayaran.
                    Jika nomor VA atau gambar QR belum muncul, periksa respons
                    channel Xendit yang digunakan dan tambahkan tampilan khusus
                    untuk data channel tersebut.
                </p>

                <form
                    action="{{ route('pembayaran.cek-status', ['bookingCode' => $booking->booking_code]) }}"
                    method="POST"
                    class="mt-6"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="transaction_id"
                        value="{{ $transaction->id }}"
                    >

                    <button
                        type="submit"
                        class="w-full rounded-xl border border-green-600 px-5 py-3 font-semibold text-green-700 hover:bg-green-50"
                    >
                        Cek Status Pembayaran
                    </button>
                </form>
            @endif

            <a
                href="{{ route('pembayaran.mulai', ['bookingCode' => $booking->booking_code]) }}"
                class="mt-4 block text-center text-sm text-slate-600 underline"
            >
                Kembali ke metode pembayaran
            </a>
        </section>
    </main>

    @includeIf('components.footer.footer')
</body>
</html>

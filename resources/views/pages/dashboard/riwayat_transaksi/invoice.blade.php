
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Invoice {{ $booking->booking_code }} - D'JAVA TOUR AND TRAVEL</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- CSS khusus untuk kebutuhan cetak A4 --}}
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            html,
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                background: #ffffff !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .invoice-paper {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                font-size: 11px;
            }

            .invoice-paper h1 {
                font-size: 17px;
            }

            .invoice-paper .invoice-title {
                font-size: 19px;
            }

            .invoice-paper table th,
            .invoice-paper table td {
                padding: 7px;
            }

            .invoice-description {
                min-height: 300px;
            }

            .invoice-signature-space {
                height: 60px;
            }

            .invoice-header,
            .invoice-payment-information,
            .invoice-footer {
                break-inside: avoid;
            }

            .invoice-payment-information > div {
                padding: 9px;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">

    {{-- Navbar website --}}
    <div class="no-print">
        @include('components.navbar.navbar')
    </div>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Tombol navigasi dan cetak --}}
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">

            <a
                href="{{ localized_route('customer.bookings.index') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
            >
                <span aria-hidden="true" class="mr-2">←</span>
                Kembali ke Riwayat
            </a>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-6 py-2 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                >
                    Cetak Invoice
                </button>

                <button
                    type="button"
                    onclick="navigator.clipboard.writeText(window.location.href)"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Salin Link
                </button>

            </div>
        </div>

        {{-- Dokumen invoice --}}
        <article class="invoice-paper mx-auto w-full max-w-5xl overflow-hidden rounded-xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-900 shadow-sm sm:p-8 lg:p-10">

            {{-- Header perusahaan --}}
            <header class="invoice-header mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2">

                <div class="break-words">
                    <h1 class="mb-2 text-xl font-extrabold uppercase sm:text-2xl">
                        {{ $company['name'] }}
                    </h1>

                    @if ($company['address'])
                        <p>{{ $company['address'] }}</p>
                    @endif

                    @if ($company['phone'])
                        <p>Telepon: {{ $company['phone'] }}</p>
                    @endif

                    @if ($company['email'])
                        <p>Email: {{ $company['email'] }}</p>
                    @endif

                    @if ($company['website'])
                        <p>{{ $company['website'] }}</p>
                    @endif
                </div>

                <div class="text-left sm:text-right">

                    <h2 class="invoice-title border border-slate-900 px-3 py-2 text-center text-xl font-extrabold">
                        INVOICE
                    </h2>

                    <div class="mt-4 flex flex-col break-words">
                        <strong class="text-base">
                            {{ $booking->customer_name }}
                        </strong>

                        <span>
                            {{ $booking->pickup_location ?: '-' }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-1 break-words">
                        <p>
                            <strong>No. Invoice:</strong>
                            {{ $invoiceNumber }}
                        </p>

                        <p>
                            <strong>Tanggal Invoice:</strong>
                            {{ $invoiceDate }}
                        </p>
                    </div>

                </div>

            </header>

            {{-- Rincian booking --}}
            <div class="overflow-x-auto">
                <table class="w-full table-fixed border-collapse text-xs sm:text-sm">

                    <thead>
                        <tr>
                            <th class="w-10 border border-slate-900 p-2 text-center sm:w-12">
                                No.
                            </th>

                            <th class="w-20 border border-slate-900 p-2 text-center sm:w-24">
                                Tanggal
                            </th>

                            <th class="w-12 border border-slate-900 p-2 text-center sm:w-16">
                                Pax
                            </th>

                            <th class="border border-slate-900 p-2 text-center">
                                Keterangan
                            </th>

                            <th class="w-24 border border-slate-900 p-2 text-center sm:w-32">
                                Harga (Rp)
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="border border-slate-900 p-2 text-center align-top">
                                1
                            </td>

                            <td class="border border-slate-900 p-2 text-center align-top">
                                {{ $travelDate }}
                            </td>

                            <td class="border border-slate-900 p-2 text-center align-top">
                                {{ $booking->pax }}
                            </td>

                            <td class="invoice-description min-h-80 border border-slate-900 p-3 align-top">
                                <div class="space-y-3 break-words">

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            Nama Klien:
                                        </strong>
                                        {{ $booking->customer_name }}
                                    </p>

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            Email:
                                        </strong>
                                        {{ $booking->customer_email ?: '-' }}
                                    </p>

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            No. WhatsApp:
                                        </strong>
                                        {{ $booking->customer_phone ?: '-' }}
                                    </p>

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            Layanan:
                                        </strong>
                                        {{ $invoiceService }}
                                    </p>

                                    @if ($booking->selected_armada)
                                        <p>
                                            <strong class="inline-block min-w-28 sm:min-w-32">
                                                Armada:
                                            </strong>
                                            {{ $booking->selected_armada }}
                                        </p>
                                    @endif

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            Durasi:
                                        </strong>
                                        {{ $invoiceDuration }}
                                    </p>

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            Jadwal:
                                        </strong>
                                        {{ $travelDate }}
                                    </p>

                                    <p>
                                        <strong class="inline-block min-w-28 sm:min-w-32">
                                            Penjemputan:
                                        </strong>
                                        {{ $booking->pickup_location ?: '-' }}
                                    </p>

                                    @if ($booking->pickup_details)
                                        <p>
                                            <strong class="inline-block min-w-28 sm:min-w-32">
                                                Detail Lokasi:
                                            </strong>
                                            {{ $booking->pickup_details }}
                                        </p>
                                    @endif

                                    @if ($package?->description)
                                        <p>
                                            <strong class="inline-block min-w-28 sm:min-w-32">
                                                Keterangan:
                                            </strong>
                                            {{ $package->description }}
                                        </p>
                                    @endif

                                </div>
                            </td>

                            <td class="border border-slate-900 p-2 text-right align-top">
                                {{ number_format($totalPrice, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="font-bold">
                            <td colspan="4" class="border border-slate-900 p-2 text-right">
                                Total
                            </td>

                            <td class="border border-slate-900 p-2 text-right">
                                Rp {{ number_format($totalPrice, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4" class="border border-slate-900 p-2 text-right">
                                Down Payment (30%)
                            </td>

                            <td class="border border-slate-900 p-2 text-right">
                                Rp {{ number_format($dpAmount, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4" class="border border-slate-900 p-2 text-right">
                                Sudah Dibayar
                            </td>

                            <td class="border border-slate-900 p-2 text-right">
                                Rp {{ number_format($paidAmount, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="font-bold">
                            <td colspan="4" class="border border-slate-900 p-2 text-right">
                                Sisa Pembayaran
                            </td>

                            <td class="border border-slate-900 p-2 text-right">
                                Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Status pembayaran --}}
            <div class="mt-4 border border-slate-900 p-3 font-bold">
                Status Pembayaran:

                @switch($invoiceStatus)
                    @case('cancelled')
                        DIBATALKAN
                        @break

                    @case('paid')
                        LUNAS
                        @break

                    @case('partial')
                        PEMBAYARAN SEBAGIAN
                        @break

                    @default
                        BELUM DIBAYAR
                @endswitch
            </div>

            {{-- Informasi pembayaran --}}
            <section class="invoice-payment-information mt-4 grid grid-cols-1 border border-slate-900 sm:grid-cols-2">

                <div class="p-3">
                    <h2 class="mb-3 font-bold">
                        Informasi Pembayaran
                    </h2>

                    <p class="break-words">
                        <strong>Metode:</strong>
                        {{ $paymentMethod }}
                    </p>

                    @forelse ($paidTransactions as $transaction)
                        <p class="mt-2 break-words">
                            <strong>
                                {{ $transaction->payment_stage === 'dp' ? 'DP' : 'Pelunasan' }}:
                            </strong>

                            Rp {{ number_format((int) $transaction->amount, 0, ',', '.') }}
                        </p>

                        <p class="break-words">
                            <strong>Kode Transaksi:</strong>
                            {{ $transaction->transaction_code }}
                        </p>
                    @empty
                        <p>Belum terdapat pembayaran berhasil.</p>
                    @endforelse
                </div>

                <div class="border-t border-slate-900 p-3 sm:border-l sm:border-t-0">

                    <h2 class="mb-3 font-bold">
                        Informasi Rekening Perusahaan
                    </h2>

                    @if ($company['bank_name'] || $company['bank_account'] || $company['bank_holder'])

                        @if ($company['bank_name'])
                            <p>{{ $company['bank_name'] }}</p>
                        @endif

                        @if ($company['bank_holder'])
                            <p>Atas Nama: {{ $company['bank_holder'] }}</p>
                        @endif

                        @if ($company['bank_account'])
                            <p>No. Rekening: {{ $company['bank_account'] }}</p>
                        @endif

                    @else
                        <p>Informasi rekening belum dikonfigurasi.</p>
                    @endif

                </div>

            </section>

            {{-- Footer invoice --}}
            <footer class="invoice-footer mt-8">

                <p>
                    Terima kasih telah menggunakan layanan
                    {{ $company['name'] }}.
                </p>

                <div class="mt-6">
                    <p>Hormat Kami,</p>

                    <strong>{{ $company['name'] }}</strong>

                    <div class="invoice-signature-space h-20"></div>

                    <p>Administrasi</p>
                </div>

            </footer>

        </article>

    </main>

    {{-- Footer website --}}
    <div class="no-print">
        @include('components.footer.footer')
    </div>

</body>
</html>

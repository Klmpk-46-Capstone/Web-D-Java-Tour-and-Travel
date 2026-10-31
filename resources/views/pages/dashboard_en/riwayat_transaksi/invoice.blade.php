
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Invoice {{ $booking->booking_code }} - D'JAVA TOUR AND TRAVEL</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .invoice-document {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background: #ffffff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .invoice-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 25px;
        }

        .invoice-company-name {
            margin-bottom: 8px;
            font-size: 21px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .invoice-company p {
            margin: 2px 0;
            overflow-wrap: anywhere;
        }

        .invoice-heading {
            text-align: right;
        }

        .invoice-heading h1 {
            border: 1px solid #111827;
            padding: 7px;
            font-size: 22px;
            font-weight: 800;
            text-align: center;
        }

        .invoice-customer {
            display: flex;
            flex-direction: column;
            margin-top: 18px;
        }

        .invoice-customer strong {
            font-size: 17px;
        }

        .invoice-meta {
            margin-top: 18px;
        }

        .invoice-meta p {
            margin: 3px 0;
            overflow-wrap: anywhere;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #111827;
            padding: 10px;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .invoice-table th {
            font-weight: 700;
            text-align: center;
        }

        .invoice-number-column {
            width: 6%;
        }

        .invoice-date-column {
            width: 16%;
        }

        .invoice-pax-column {
            width: 8%;
        }

        .invoice-price-column {
            width: 20%;
        }

        .invoice-description {
            min-height: 350px;
        }

        .invoice-description p {
            margin: 0 0 10px;
        }

        .invoice-description strong {
            display: inline-block;
            min-width: 125px;
        }

        .invoice-total-row {
            font-weight: 700;
        }

        .invoice-payment-status {
            margin-top: 15px;
            border: 1px solid #111827;
            padding: 12px;
            font-weight: 700;
        }

        .invoice-payment-information {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 15px;
            border: 1px solid #111827;
        }

        .invoice-payment-information > div {
            padding: 12px;
        }

        .invoice-payment-information > div + div {
            border-left: 1px solid #111827;
        }

        .invoice-payment-information h2 {
            margin-bottom: 10px;
            font-size: 15px;
            font-weight: 700;
        }

        .invoice-payment-information p {
            margin: 4px 0;
            overflow-wrap: anywhere;
        }

        .invoice-footer {
            margin-top: 30px;
        }

        .invoice-signature {
            margin-top: 20px;
        }

        .invoice-signature-space {
            height: 80px;
        }

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

            .invoice-document {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
                font-size: 11px;
            }

            .invoice-company-name {
                font-size: 17px;
            }

            .invoice-heading h1 {
                font-size: 19px;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 7px;
            }

            .invoice-description {
                min-height: 300px;
            }

            .invoice-payment-information > div {
                padding: 9px;
            }

            .invoice-signature-space {
                height: 60px;
            }

            .invoice-header,
            .invoice-payment-information,
            .invoice-footer {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">

    @php
        $package = $booking->package;
        $trip = $booking->trip;

        $transactions = $booking->transactions;

        $paidStatuses = [
            'success',
            'paid',
            'succeeded',
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

        $totalPrice = (int) $booking->total_price;
        $paidAmount = (int) $paidTransactions->sum('amount');

        $remainingAmount = max(0, $totalPrice - $paidAmount);
        $dpAmount = (int) round($totalPrice * 0.30);

        $isPaid = (
            $totalPrice > 0
            && $paidAmount >= $totalPrice
        ) || in_array(
            strtolower((string) $booking->payment_status),
            ['paid', 'lunas', 'settled', 'success'],
            true
        );

        $bookingStatus = strtolower((string) $booking->booking_status);

        $isCancelled = in_array(
            $bookingStatus,
            ['cancelled', 'canceled'],
            true
        );

        $invoiceStatus = match (true) {
            $isCancelled => 'DIBATALKAN',
            $isPaid => 'LUNAS',
            $paidAmount > 0 => 'PEMBAYARAN SEBAGIAN',
            default => 'BELUM DIBAYAR',
        };

        $invoiceDate = $booking->created_at
            ? $booking->created_at->translatedFormat('d F Y')
            : '-';

        $departureDate = $trip?->departure_date;

        $returnDate = $trip?->return_date;

        $travelDate = $departureDate
            ? \Illuminate\Support\Carbon::parse($departureDate)->translatedFormat('d F Y')
            : '-';

        if ($returnDate) {
            $travelDate .= ' - ' . \Illuminate\Support\Carbon::parse($returnDate)->translatedFormat('d F Y');
        }

        $invoiceNumber = 'INV-' . $booking->booking_code;

        $invoiceService = $package?->name
            ?? $booking->selected_armada
            ?? 'Layanan perjalanan';

        $invoiceDuration = $package?->duration ?? '-';

        $invoiceCompanyName = config('app.company_name')
            ?: "D'JAVA TOUR AND TRAVEL";

        $invoiceCompanyAddress = config('app.company_address');
        $invoiceCompanyPhone = config('app.company_phone');
        $invoiceCompanyEmail = config('app.company_email');
        $invoiceCompanyWebsite = config('app.company_website');

        $invoiceBankName = config('app.company_bank_name');
        $invoiceBankAccount = config('app.company_bank_account');
        $invoiceBankHolder = config('app.company_bank_holder');

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
            'CARDS' => 'Credit / Debit Card',
            'ALFAMART' => 'Alfamart',
            'INDOMARET' => 'Indomaret',
        ];

        $latestPaidTransaction = $paidTransactions
            ->sortByDesc('id')
            ->first();

        $rawPaymentMethod = $latestPaidTransaction?->payment_channel
            ?: $latestPaidTransaction?->payment_method
            ?: $booking->payment_method
            ?: '-';

        $paymentMethod = $channelLabels[strtoupper($rawPaymentMethod)]
            ?? $rawPaymentMethod;
    @endphp

    {{-- Navbar: tidak ditampilkan ketika cetak --}}
    <div class="no-print">
        @include('components.navbar.navbar')
    </div>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Tombol Aksi --}}
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">

            <a
                href="{{ localized_route('customer.bookings.index') }}"
                class="inline-flex h-11 items-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
            >
                ← Kembali ke Riwayat
            </a>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex h-11 items-center justify-center rounded-lg bg-teal-700 px-6 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                >
                    Cetak Invoice
                </button>

                <button
                    type="button"
                    onclick="navigator.clipboard.writeText(window.location.href)"
                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                >
                    Copy Link
                </button>

            </div>
        </div>

        {{-- Dokumen Invoice --}}
        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="invoice-document">

                {{-- Header Perusahaan --}}
                <header class="invoice-header">

                    <div class="invoice-company">

                        <h1 class="invoice-company-name">
                            {{ $invoiceCompanyName }}
                        </h1>

                        @if ($invoiceCompanyAddress)
                            <p>{{ $invoiceCompanyAddress }}</p>
                        @endif

                        @if ($invoiceCompanyPhone)
                            <p>Telepon: {{ $invoiceCompanyPhone }}</p>
                        @endif

                        @if ($invoiceCompanyEmail)
                            <p>Email: {{ $invoiceCompanyEmail }}</p>
                        @endif

                        @if ($invoiceCompanyWebsite)
                            <p>{{ $invoiceCompanyWebsite }}</p>
                        @endif

                    </div>

                    <div class="invoice-heading">

                        <h1>INVOICE</h1>

                        <div class="invoice-customer">
                            <strong>{{ $booking->customer_name }}</strong>

                            <span>
                                {{ $booking->pickup_location ?: '-' }}
                            </span>
                        </div>

                        <div class="invoice-meta">
                            <p>
                                <strong>No. Invoice:</strong>
                                {{ $invoiceNumber }}
                            </p>

                            <p>
                                <strong>Tgl. Invoice:</strong>
                                {{ $invoiceDate }}
                            </p>
                        </div>

                    </div>

                </header>

                {{-- Rincian Pemesanan --}}
                <table class="invoice-table">

                    <thead>
                        <tr>
                            <th class="invoice-number-column">No.</th>
                            <th class="invoice-date-column">Tanggal</th>
                            <th class="invoice-pax-column">Pax</th>
                            <th>Keterangan</th>
                            <th class="invoice-price-column">Harga (Rp)</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="text-center">1</td>

                            <td class="text-center">
                                {{ $travelDate }}
                            </td>

                            <td class="text-center">
                                {{ $booking->pax }}
                            </td>

                            <td class="invoice-description">

                                <p>
                                    <strong>Nama Klien:</strong>
                                    {{ $booking->customer_name }}
                                </p>

                                <p>
                                    <strong>Email:</strong>
                                    {{ $booking->customer_email ?: '-' }}
                                </p>

                                <p>
                                    <strong>No. WhatsApp:</strong>
                                    {{ $booking->customer_phone ?: '-' }}
                                </p>

                                <p>
                                    <strong>Layanan:</strong>
                                    {{ $invoiceService }}
                                </p>

                                @if ($booking->selected_armada)
                                    <p>
                                        <strong>Armada:</strong>
                                        {{ $booking->selected_armada }}
                                    </p>
                                @endif

                                <p>
                                    <strong>Durasi:</strong>
                                    {{ $invoiceDuration }}
                                </p>

                                <p>
                                    <strong>Jadwal:</strong>
                                    {{ $travelDate }}
                                </p>

                                <p>
                                    <strong>Penjemputan:</strong>
                                    {{ $booking->pickup_location ?: '-' }}
                                </p>

                                @if ($booking->pickup_details)
                                    <p>
                                        <strong>Detail Lokasi:</strong>
                                        {{ $booking->pickup_details }}
                                    </p>
                                @endif

                                @if ($package?->description)
                                    <p>
                                        <strong>Keterangan:</strong>
                                        {{ $package->description }}
                                    </p>
                                @endif

                            </td>

                            <td class="text-right">
                                {{ number_format($totalPrice, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="invoice-total-row">
                            <td colspan="4" class="text-right">
                                Total
                            </td>
                            <td class="text-right">
                                Rp {{ number_format($totalPrice, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4" class="text-right">
                                Down Payment (30%)
                            </td>
                            <td class="text-right">
                                Rp {{ number_format($dpAmount, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4" class="text-right">
                                Sudah Dibayar
                            </td>
                            <td class="text-right">
                                Rp {{ number_format($paidAmount, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="invoice-total-row">
                            <td colspan="4" class="text-right">
                                Sisa Pembayaran
                            </td>
                            <td class="text-right">
                                Rp {{ number_format($remainingAmount, 0, ',', '.') }}
                            </td>
                        </tr>

                    </tbody>
                </table>

                {{-- Status Pembayaran --}}
                <div class="invoice-payment-status">
                    Status Pembayaran: {{ $invoiceStatus }}
                </div>

                {{-- Informasi Pembayaran --}}
                <section class="invoice-payment-information">

                    <div>
                        <h2>Informasi Pembayaran</h2>

                        <p>
                            <strong>Metode:</strong>
                            {{ $paymentMethod }}
                        </p>

                        @forelse ($paidTransactions as $transaction)
                            <p>
                                <strong>
                                    {{ $transaction->payment_stage === 'dp' ? 'DP' : 'Pelunasan' }}:
                                </strong>

                                Rp {{ number_format((int) $transaction->amount, 0, ',', '.') }}
                            </p>

                            <p>
                                <strong>Kode:</strong>
                                {{ $transaction->transaction_code }}
                            </p>
                        @empty
                            <p>Belum terdapat pembayaran berhasil.</p>
                        @endforelse
                    </div>

                    <div>
                        <h2>Informasi Rekening Perusahaan</h2>

                        @if ($invoiceBankName || $invoiceBankAccount || $invoiceBankHolder)

                            @if ($invoiceBankName)
                                <p>{{ $invoiceBankName }}</p>
                            @endif

                            @if ($invoiceBankHolder)
                                <p>Atas Nama: {{ $invoiceBankHolder }}</p>
                            @endif

                            @if ($invoiceBankAccount)
                                <p>No. Rekening: {{ $invoiceBankAccount }}</p>
                            @endif

                        @else
                            <p>Informasi rekening belum dikonfigurasi.</p>
                        @endif
                    </div>

                </section>

                {{-- Footer Invoice --}}
                <footer class="invoice-footer">

                    <p>
                        Terima kasih telah menggunakan layanan
                        {{ $invoiceCompanyName }}.
                    </p>

                    <div class="invoice-signature">

                        <p>Hormat Kami,</p>

                        <strong>{{ $invoiceCompanyName }}</strong>

                        <div class="invoice-signature-space"></div>

                        <p>Administrasi</p>

                    </div>

                </footer>

            </div>

        </article>

    </main>

    {{-- Footer Website: tidak ditampilkan ketika cetak --}}
    <div class="no-print">
        @include('components.footer.footer')
    </div>

</body>
</html>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengajuan Refund - D'Java Tour &amp; Travel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

@include('components.navbar.navbar')

<main class="mx-auto max-w-7xl px-5 py-8 sm:px-7 lg:px-8">

    <nav class="mb-5 flex flex-wrap items-center gap-3 text-sm font-medium text-slate-500">
        <a href="{{ localized_route('customer.bookings.detail', ['bookingCode' => $booking->booking_code]) }}"
           class="text-teal-700 hover:text-teal-900">
            ← Kembali ke Detail Invoice
        </a>
        <span>/</span>
        <span class="rounded-md bg-slate-100 px-3 py-1 font-mono text-slate-700">
            #{{ $booking->booking_code }}
        </span>
        <span>/</span>
        <span class="text-slate-800">Pengajuan Refund</span>
    </nav>

    <div class="mb-7 border-b border-slate-200 pb-5">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
            Kebijakan &amp; Pengajuan Refund
        </h1>
        <p class="mt-2 text-base text-slate-500">
            Kelola pembatalan reservasi dan pengembalian dana deposit sesuai ketentuan layanan.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-teal-200 bg-teal-50 p-4 text-base font-medium text-teal-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-base text-rose-800">
            <p class="font-bold">Periksa kembali data pengajuan Anda.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $packageName = $booking->package?->name
            ?? $booking->selected_armada
            ?? 'Layanan perjalanan';

        $departureDate = $booking->trip?->departure_date
            ?? $booking->rental_date;

        $travelDate = $departureDate
            ? \Illuminate\Support\Carbon::parse($departureDate)->translatedFormat('d M Y')
            : 'Jadwal belum tersedia';

        $daysRemaining = $refundData['daysRemaining'];

        $daysLabel = $daysRemaining === null
            ? '-'
            : ($daysRemaining >= 0
                ? 'H-' . $daysRemaining
                : 'Lewat ' . abs($daysRemaining) . ' hari');

        $refundExists = $refund !== null;

        $refundStatusLabels = [
            'pending' => 'Menunggu Pemeriksaan',
            'processing' => 'Sedang Diproses',
            'approved' => 'Disetujui - Menunggu Transfer',
            'rejected' => 'Pengajuan Ditolak',
            'refunded' => 'Dana Telah Dikembalikan',
        ];

        $activeRefund = $refundExists
            && in_array($refund->status, ['pending', 'processing', 'approved'], true);
    @endphp

    <section class="mb-6 grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 xl:grid-cols-4">

        <div class="flex items-center gap-4 border-b border-slate-100 pb-4 sm:border-b-0 sm:border-r sm:pb-0">
            <div class="min-w-0">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-400">No. Invoice</p>
                <p class="mt-1 break-all font-mono text-base font-bold text-slate-800">
                    #{{ $booking->booking_code }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 border-b border-slate-100 pb-4 sm:border-b-0 xl:border-r xl:pb-0">
            <div class="min-w-0">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-400">Paket Wisata</p>
                <p class="mt-1 truncate text-base font-bold text-slate-800">
                    {{ $packageName }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 border-b border-slate-100 pb-4 sm:border-b-0 sm:border-r sm:pb-0">
            <div class="min-w-0">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-400">Jadwal Perjalanan</p>
                <p class="mt-1 text-base font-bold text-slate-800">
                    {{ $travelDate }}
                    <span class="text-teal-700">({{ $daysLabel }})</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="min-w-0">
                <p class="text-sm font-medium uppercase tracking-wide text-slate-400">Total Pembayaran Berhasil</p>
                <p class="mt-1 text-xl font-extrabold text-slate-900">
                    Rp {{ number_format($refundData['paidAmount'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-5">

        {{-- Ketentuan refund --}}
        <aside class="space-y-5 lg:col-span-2">

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <h2 class="text-xl font-bold text-slate-900">
                        Ketentuan Refund
                    </h2>
                    <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Kebijakan Refund
                    </span>
                </div>

                <p class="mt-4 text-base leading-7 text-slate-500">
                    Nominal pengembalian dana dihitung berdasarkan sisa hari sebelum tanggal keberangkatan dan total pembayaran yang berhasil.
                </p>

                <div class="mt-5 space-y-3">

                    <div @class([
                        'rounded-xl border-2 p-4',
                        'border-teal-500 bg-teal-50' => $refundData['tier'] === 'tier_1',
                        'border-slate-200 bg-white' => $refundData['tier'] !== 'tier_1',
                    ])>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-bold text-slate-800">● Tier 1: ≥ H-7 Jadwal</p>
                            <span class="rounded-full bg-teal-100 px-3 py-1 text-sm font-bold text-teal-800">
                                100% Refund
                            </span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Pengembalian dana penuh dari pembayaran berhasil tanpa potongan.
                        </p>
                        @if ($refundData['tier'] === 'tier_1')
                            <p class="mt-3 rounded-lg bg-teal-100 px-3 py-2 text-sm font-semibold text-teal-800">
                                ✓ Status Saat Ini: Memenuhi Kriteria ({{ $daysLabel }})
                            </p>
                        @endif
                    </div>

                    <div @class([
                        'rounded-xl border-2 p-4',
                        'border-amber-400 bg-amber-50' => $refundData['tier'] === 'tier_2',
                        'border-slate-200 bg-white' => $refundData['tier'] !== 'tier_2',
                    ])>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-bold text-slate-800">● Tier 2: H-6 s/d H-3 Jadwal</p>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">
                                50% Refund
                            </span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Dipotong komitmen armada dan reservasi tiket masuk kawasan.
                        </p>
                        @if ($refundData['tier'] === 'tier_2')
                            <p class="mt-3 rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-800">
                                ✓ Status Saat Ini: Memenuhi Kriteria ({{ $daysLabel }})
                            </p>
                        @endif
                    </div>

                    <div @class([
                        'rounded-xl border-2 p-4',
                        'border-rose-400 bg-rose-50' => $refundData['tier'] === 'tier_3',
                        'border-slate-200 bg-white' => $refundData['tier'] !== 'tier_3',
                    ])>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-bold text-slate-800">● Tier 3: < H-3 / Hari H</p>
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-sm font-bold text-rose-800">
                                0% Refund
                            </span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Tidak mendapatkan pengembalian dana sesuai ketentuan pembatalan.
                        </p>
                        @if ($refundData['tier'] === 'tier_3')
                            <p class="mt-3 rounded-lg bg-rose-100 px-3 py-2 text-sm font-semibold text-rose-800">
                                ✓ Status Saat Ini: {{ $daysLabel }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="mt-5 border-t border-slate-100 pt-5">
                    <p class="flex items-start gap-3 text-sm leading-6 text-slate-600">
                        <span class="text-xl text-teal-700">◷</span>
                        <span>
                            <strong class="text-slate-800">Estimasi Pencairan:</strong>
                            1–3 hari kerja setelah persetujuan admin dan proses transfer manual.
                        </span>
                    </p>
                </div>
            </section>

            <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5">
                <div>
                    <h3 class="font-bold text-slate-900">Butuh reschedule tanggal?</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Konsultasikan perubahan jadwal kepada tim kami.
                    </p>
                </div>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('app.company_phone', '')) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-bold text-teal-800 hover:bg-teal-100">
                    WhatsApp CS
                </a>
            </section>
        </aside>

        {{-- Form pengajuan --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">

            @if ($refundExists)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h2 class="text-xl font-bold text-slate-900">Pengajuan Refund Tercatat</h2>
                    <p class="mt-2 text-base text-slate-600">
                        Kode Refund:
                        <strong class="font-mono">{{ $refund->refund_code }}</strong>
                    </p>
                    <p class="mt-3 text-base text-slate-600">
                        Status:
                        <strong class="text-amber-800">
                            {{ $refundStatusLabels[$refund->status] ?? ucfirst($refund->status) }}
                        </strong>
                    </p>
                    <p class="mt-2 text-base text-slate-600">
                        Nominal pengajuan:
                        <strong>Rp {{ number_format($refund->amount, 0, ',', '.') }}</strong>
                    </p>
                    @if ($refund->admin_notes)
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Catatan admin: {{ $refund->admin_notes }}
                        </p>
                    @endif
                    @if ($refund->transferred_at)
                        <p class="mt-3 text-sm font-semibold text-teal-800">
                            Dana ditransfer pada {{ $refund->transferred_at->translatedFormat('d F Y, H:i') }}
                        </p>
                    @endif
                </div>
            @elseif (!$refundData['eligible'])
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-base text-amber-900">
                    Belum ada pembayaran berhasil untuk booking ini. Pengajuan refund belum dapat dilakukan.
                </div>
            @else
                <form method="POST"
                      action="{{ localized_route('customer.bookings.refund.submit', ['bookingCode' => $booking->booking_code]) }}"
                      class="space-y-7">
                    @csrf

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">1</span>
                            <h2 class="text-lg font-bold uppercase tracking-wide text-slate-900">
                                Alasan Pembatalan Reservasi <span class="text-rose-600">*</span>
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ([
                                'schedule_change' => 'Perubahan Jadwal',
                                'weather' => 'Cuaca Buruk / Bencana',
                                'emergency' => 'Keperluan Mendesak',
                                'other' => 'Alasan Lainnya',
                            ] as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 text-base font-medium transition hover:border-teal-400 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50">
                                    <input type="radio"
                                           name="reason"
                                           value="{{ $value }}"
                                           required
                                           class="h-5 w-5 accent-teal-600"
                                           @checked(old('reason') === $value)>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <label class="mt-4 block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">
                                Keterangan tambahan (opsional)
                            </span>
                            <textarea name="reason_detail"
                                      rows="3"
                                      maxlength="1000"
                                      class="w-full rounded-xl border border-slate-200 px-4 py-3 text-base outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                      placeholder="Jelaskan alasan pembatalan jika diperlukan...">{{ old('reason_detail') }}</textarea>
                        </label>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">2</span>
                            <h2 class="text-lg font-bold uppercase tracking-wide text-slate-900">
                                Rekening Pengembalian Dana <span class="text-rose-600">*</span>
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label>
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Bank Pilihan</span>
                                <select name="bank_name"
                                        required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                                    <option value="">Pilih bank</option>
                                    @foreach (['BCA', 'BNI', 'BRI', 'Mandiri', 'BSI', 'Permata', 'CIMB Niaga', 'Danamon', 'Bank Lainnya'] as $bank)
                                        <option value="{{ $bank }}" @selected(old('bank_name') === $bank)>
                                            {{ $bank }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="mb-2 block text-sm font-semibold text-slate-700">Nomor Rekening</span>
                                <input type="text"
                                       name="bank_account"
                                       value="{{ old('bank_account') }}"
                                       inputmode="numeric"
                                       pattern="[0-9]{8,25}"
                                       minlength="8"
                                       maxlength="25"
                                       autocomplete="off"
                                       required
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 font-mono text-base outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                       placeholder="Masukkan nomor rekening">
                            </label>
                        </div>

                        <label class="mt-4 block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">Nama Pemilik Rekening</span>
                            <input type="text"
                                   name="account_holder"
                                   value="{{ old('account_holder') }}"
                                   maxlength="150"
                                   autocomplete="off"
                                   required
                                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-base outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
                                   placeholder="Nama sesuai rekening bank">
                        </label>

                        <p class="mt-3 flex items-start gap-2 text-sm leading-6 text-slate-400">
                            <span class="font-bold text-teal-700">ⓘ</span>
                            Pastikan nama pemilik rekening sesuai dengan data rekening tujuan. Informasi rekening akan dienkripsi saat disimpan.
                        </p>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">3</span>
                            <h2 class="text-lg font-bold uppercase tracking-wide text-slate-900">
                                Ringkasan Pengembalian Dana
                            </h2>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                            <div class="space-y-3 text-base">
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Total DP / Pembayaran Berhasil</span>
                                    <strong class="text-right font-mono text-slate-800">
                                        Rp {{ number_format($refundData['paidAmount'], 0, ',', '.') }}
                                    </strong>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Persentase Pengembalian</span>
                                    <strong class="text-right text-teal-700">
                                        {{ $refundData['percentage'] }}%
                                    </strong>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500">Biaya Administrasi / Potongan</span>
                                    <strong class="text-right font-mono text-slate-800">
                                        Rp {{ number_format($refundData['paidAmount'] - $refundData['refundAmount'], 0, ',', '.') }}
                                    </strong>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col justify-between gap-3 rounded-xl border border-teal-200 bg-white p-5 sm:flex-row sm:items-center">
                                <div>
                                    <p class="font-bold text-slate-900">Estimasi Dana Dikembalikan</p>
                                    <p class="mt-1 text-sm text-slate-400">
                                        Nominal akhir menunggu persetujuan admin.
                                    </p>
                                </div>
                                <p class="text-3xl font-extrabold tracking-tight text-teal-600">
                                    Rp {{ number_format($refundData['refundAmount'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-5">
                        <label class="flex cursor-pointer items-start gap-3 text-sm leading-6 text-slate-600">
                            <input type="checkbox"
                                   name="agreement"
                                   value="1"
                                   required
                                   class="mt-1 h-5 w-5 shrink-0 accent-teal-600"
                                   @checked(old('agreement'))>
                            <span>
                                Saya mengonfirmasi pembatalan reservasi ini dan menyetujui ketentuan serta regulasi refund D'Java Tour &amp; Travel. Saya memahami bahwa pengajuan akan diperiksa admin dan transfer dilakukan secara manual setelah disetujui.
                            </span>
                        </label>
                    </div>

                    <div class="flex flex-col-reverse justify-between gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center">
                        <a href="{{ localized_route('customer.bookings.detail', ['bookingCode' => $booking->booking_code]) }}"
                           class="inline-flex min-h-12 items-center justify-center rounded-xl px-5 py-3 font-semibold text-slate-600 hover:bg-slate-100">
                            Batal
                        </a>

                        <button type="submit"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-teal-600 px-7 py-3 text-base font-bold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                            ➤ Kirim Pengajuan Refund
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</main>

@include('components.footer.footer')

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Pemesanan &amp; E-Tiket - D'Java Tour &amp; Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', Arial, sans-serif; }
    </style>
</head>
<body class="bg-indigo-50/40 text-slate-800 antialiased min-h-screen flex flex-col">
    @include('components.navbar.navbar')

    <main class="flex-1 py-8 md:py-12">
        <div class="container mx-auto px-4 max-w-4xl">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-900">&times;</button>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-bold flex items-center justify-between">
                    <span>{{ session('warning') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-amber-900">&times;</button>
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-8">
                <span class="text-xs font-extrabold uppercase tracking-wider text-teal-700 block mb-1">
                    DASHBOARD CUSTOMER
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Riwayat Pemesanan &amp; Status E-Tiket
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau status reservasi, tiket aktif, serta proses verifikasi pembatalan &amp; pengembalian dana (refund).
                </p>
            </div>

            {{-- Bookings List --}}
            <div class="space-y-4">
                @forelse ($bookings as $b)
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-md transition">
                        <div class="space-y-2 flex-1">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-mono font-black text-teal-700">
                                    #{{ $b->booking_code }}
                                </span>
                                @if ($b->booking_status === 'confirmed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        ✓ Terkonfirmasi (Tiket Aktif)
                                    </span>
                                @elseif ($b->booking_status === 'refund_requested')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 animate-pulse">
                                        ⏱ Pengajuan Refund (Sedang Direview Admin)
                                    </span>
                                @elseif ($b->booking_status === 'cancelled')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700">
                                        ✕ Dibatalkan / Dana Dikembalikan
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                        {{ ucfirst($b->booking_status) }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-extrabold text-slate-900 leading-snug">
                                {{ $b->package->name ?? 'Trip Khusus' }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span>Jadwal: <strong class="text-slate-700">{{ $b->trip && $b->trip->departure_date ? $b->trip->departure_date->translatedFormat('d M Y') : '-' }}</strong></span>
                                <span>•</span>
                                <span>Pax: <strong class="text-slate-700">{{ $b->pax }} Orang</strong></span>
                                <span>•</span>
                                <span>Metode: <strong class="text-slate-700">{{ $b->payment_method }}</strong></span>
                            </div>

                            @if ($b->refund)
                                <div class="mt-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-slate-600">
                                    <strong>Status Klaim:</strong> {{ strtoupper($b->refund->status) }}
                                    @if ($b->refund->admin_notes)
                                        <div class="text-[11px] text-teal-800 mt-0.5">
                                            <strong>Respon Admin CMS:</strong> {{ $b->refund->admin_notes }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Total & Actions --}}
                        <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center border-t md:border-t-0 pt-4 md:pt-0 border-slate-100 gap-3">
                            <div class="text-left md:text-right">
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Total Bayar</span>
                                <span class="text-lg font-black text-slate-900">Rp {{ number_format($b->total_price, 0, ',', '.') }}</span>
                            </div>

                            {{-- Point 4: Cancellation & Refund button --}}
                            @if ($b->booking_status === 'confirmed')
                                <button 
                                    type="button" 
                                    onclick="openRefundModal('{{ $b->booking_code }}', '{{ $b->package->name ?? '' }}')"
                                    class="px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition cursor-pointer"
                                >
                                    Ajukan Pembatalan / Refund
                                </button>
                            @elseif ($b->booking_status === 'refund_requested')
                                <span class="text-xs font-bold text-amber-600">
                                    Menunggu Approval CMS
                                </span>
                            @else
                                <span class="text-xs font-bold text-slate-400">
                                    Selesai
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center text-slate-400">
                        <strong class="text-base text-slate-800 block mb-1">Belum Ada Riwayat Reservasi</strong>
                        Silakan jelajahi katalog paket wisata kami untuk memesan trip pertama Anda.
                        <div class="mt-4">
                            <a href="{{ route('packages.index') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition">
                                Eksplorasi Paket Wisata &rarr;
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </main>

    {{-- MODAL REFUND REQUEST --}}
    <div id="modalRefund" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in duration-200">
            <button type="button" onclick="document.getElementById('modalRefund').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-2xl font-bold">&times;</button>
            <div class="flex items-center gap-2.5 mb-3">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-rose-100 text-rose-700 text-sm font-bold">!</span>
                <h3 class="text-lg font-extrabold text-slate-900">Form Pengajuan Refund</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4" id="refundModalSubtitle">
                Pengajuan ini akan dikirim langsung ke antrean review panel admin D'Java Tour.
            </p>

            <form id="refundForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan / Kendala Perjalanan</label>
                    <textarea 
                        name="reason" 
                        required 
                        rows="4" 
                        placeholder="Contoh: Perubahan jadwal dinas mendadak dari instansi / kendala keluarga..." 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs focus:bg-white focus:outline-none focus:border-teal-700"
                    ></textarea>
                    <span class="text-[11px] text-slate-400 block mt-1">
                        *Setelah diverifikasi admin, dana akan dikembalikan dan kuota kursi trip dibuka kembali.
                    </span>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modalRefund').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    @include('components.footer.footer')

    <script>
        function openRefundModal(bookingCode, packageName) {
            const modal = document.getElementById('modalRefund');
            const form = document.getElementById('refundForm');
            const subtitle = document.getElementById('refundModalSubtitle');
            
            form.action = '/customer/booking/' + bookingCode + '/refund-request';
            subtitle.innerText = 'Pengajuan refund untuk booking #' + bookingCode + ' (' + packageName + ') akan dikirim langsung ke tim CMS Ops Console.';
            modal.classList.remove('hidden');
        }
    </script>
</body>
</html>

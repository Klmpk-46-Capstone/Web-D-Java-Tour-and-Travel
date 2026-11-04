@extends('layouts.admin')

@section('title', 'Detail & Rekam Jejak Customer: ' . $customer->name)

@section('content')
<div class="space-y-6">

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-2xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-2xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-2xs space-y-1">
            <p class="font-bold">Terjadi kesalahan pada data yang dikirim:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- BREADCRUMB & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <a href="{{ route('admin.customers.index') }}" class="hover:text-teal-700">AKUN CUSTOMER</a>
                <span>/</span>
                <span class="text-teal-700">DETAIL &amp; AKTIVITAS</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                {{ $customer->name }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Detail profil lengkap, monitoring keaktifan login, riwayat reservasi, dan kontrol keamanan terpusat.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.customers.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                ← Kembali ke Daftar Pelanggan
            </a>
        </div>
    </div>

    {{-- BLACKLIST WARNING BANNER --}}
    @if($customer->is_blacklisted)
        <div class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 shadow-2xs flex items-start gap-4">
            <div class="p-2 rounded-xl bg-rose-100 text-rose-700 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-black text-rose-900 text-sm">AKUN CUSTOMER INI SEDANG DI-BLACKLIST (DIBLOKIR)</span>
                    <span class="text-[10px] font-bold text-rose-600 bg-rose-200/60 px-2 py-0.5 rounded-full">Akses Login Dibatasi</span>
                </div>
                <p class="text-xs text-rose-800 mt-1 font-medium">
                    <span class="font-bold">Alasan Penonaktifan:</span> {{ $customer->blacklist_reason ?: 'Pelanggaran ketentuan operasional atau verifikasi identitas.' }}
                </p>
                <p class="text-[11px] text-rose-600 mt-0.5">
                    Ditandai blacklist pada: {{ $customer->blacklisted_at ? $customer->blacklisted_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                </p>
            </div>
            <form action="{{ route('admin.customers.toggle-blacklist', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan/membuka blokir akun customer ini?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-white border border-rose-300 text-rose-700 hover:bg-rose-100 text-xs font-bold transition shadow-2xs shrink-0 cursor-pointer">
                    Buka Blokir (Pulihkan Akun)
                </button>
            </form>
        </div>
    @endif

    {{-- CUSTOMER PROFILE & METRIC SUMMARY --}}
    @php
        $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customer->name, 0, 2));
        $actBadge = $customer->activity_status_badge;
        $wa = preg_replace('/\D+/', '', (string) ($customer->whatsapp ?? ''));
        if (\Illuminate\Support\Str::startsWith($wa, '0')) { $wa = '62' . substr($wa, 1); }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Card Profil Utama Customer --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-4">
            <div class="flex items-center gap-4">
                <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-teal-50 text-xl font-black text-teal-800 ring-1 ring-teal-600/20 shadow-2xs">
                    {{ $initial }}
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-black text-slate-900 truncate">{{ $customer->name }}</h2>
                    <p class="text-xs text-slate-500 font-mono truncate">{{ $customer->email }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $actBadge['badge_class'] }}">
                            <span class="w-2 h-2 rounded-full {{ $actBadge['dot_class'] }}"></span>
                            {{ $actBadge['label'] }}
                        </span>
                        @if($customer->google_id)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 ring-1 ring-sky-500/20">Google Auth</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Nomor WhatsApp:</span>
                    @if($customer->whatsapp)
                        <div class="flex items-center gap-1.5 font-bold font-mono text-slate-800">
                            <span>{{ $customer->whatsapp }}</span>
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" title="Chat WhatsApp Customer" class="text-emerald-600 hover:text-emerald-700">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0012 0a12 12 0 00-10.3 18L0 24l6.2-1.6A12 12 0 1012 0zm-8.5 20a9.9 9.9 0 01-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.9 9.9 0 1112 23.5z"/></svg>
                            </a>
                        </div>
                    @else
                        <span class="text-slate-400 italic">Belum diisi</span>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400">No. Identitas (KTP/Paspor):</span>
                    <span class="font-bold text-slate-800 font-mono">{{ $customer->identity_number ?: 'Belum diisi' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Terdaftar Sejak:</span>
                    <span class="font-bold text-slate-800">{{ $customer->created_at->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Login Terakhir:</span>
                    <span class="font-bold text-slate-800">
                        {{ $customer->last_login_at ? $customer->last_login_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : 'Belum pernah login' }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Aktivitas Terakhir (Last Seen):</span>
                    <span class="font-bold text-slate-800">
                        {{ $customer->last_seen_at ? $customer->last_seen_at->diffForHumans() : '-' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- 4 Stat Cards --}}
        <div class="lg:col-span-2 grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Total Reservasi</span>
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-slate-900">{{ $totalBookings }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $activeBookings }} aktif / berlangsung, {{ $completedBookings }} selesai</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Total Belanja Sukses</span>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="mt-2">
                    <p class="text-xl font-black text-emerald-700">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Akumulasi pembayaran terverifikasi</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Total Pax Dipesan</span>
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-slate-900">{{ $totalPax }} <span class="text-xs font-bold text-slate-500">Orang</span></p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Akumulasi peserta rombongan</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase tracking-wider">Dibatalkan / Refund</span>
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-rose-700">{{ $cancelledBookings }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Reservasi dibatalkan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAIL RIWAYAT RESERVASI & PEMESANAN CUSTOMER --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Riwayat Reservasi &amp; Pemesanan</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-teal-50 text-teal-800">
                        {{ $bookings->count() }} Data
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rincian transparan seluruh paket yang pernah dipesan, anggota rombongan, status pembayaran, dan manifes.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">KODE &amp; TANGGAL</th>
                        <th class="py-3 px-4">PAKET &amp; JADWAL TRIP</th>
                        <th class="py-3 px-4">ANGGOTA ROMBONGAN (PAX)</th>
                        <th class="py-3 px-4">TITIK PENJEMPUTAN</th>
                        <th class="py-3 px-4">TOTAL &amp; PEMBAYARAN</th>
                        <th class="py-3 px-4 text-center">CHECK-IN</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($bookings as $b)
                        @php
                            $pkg = $b->trip?->package ?? $b->package;
                            $participants = $b->participants_list;
                            $payTone = [
                                'paid' => ['Lunas', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
                                'partial' => ['DP Terbayar', 'bg-teal-50 text-teal-700 ring-teal-600/20'],
                                'pending' => ['Menunggu Bayar', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
                                'unpaid' => ['Belum Bayar', 'bg-rose-50 text-rose-700 ring-rose-600/20'],
                            ];
                            [$payLabel, $payCls] = $payTone[$b->payment_status] ?? ['Menunggu Bayar', 'bg-slate-100 text-slate-600 ring-slate-500/20'];

                            $statusTone = [
                                'confirmed' => ['Terkonfirmasi', 'bg-teal-50 text-teal-800 ring-teal-600/20'],
                                'completed' => ['Selesai', 'bg-emerald-50 text-emerald-800 ring-emerald-600/20'],
                                'pending' => ['Menunggu Verifikasi', 'bg-amber-50 text-amber-800 ring-amber-600/20'],
                                'refund_requested' => ['Pengajuan Refund', 'bg-rose-50 text-rose-800 ring-rose-600/20'],
                                'cancelled' => ['Dibatalkan', 'bg-slate-100 text-slate-600 ring-slate-500/20'],
                            ];
                            [$stLabel, $stCls] = $statusTone[$b->booking_status] ?? [ucfirst($b->booking_status), 'bg-slate-100 text-slate-700'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <p class="font-mono font-extrabold text-teal-800 text-xs">#{{ $b->booking_code }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $b->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            </td>
                            <td class="py-3.5 px-4 max-w-[220px]">
                                <p class="font-extrabold text-slate-900 truncate">{{ $pkg?->name ?? 'Paket Wisata' }}</p>
                                @if($b->trip)
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                                        📅 {{ $b->trip->departure_date->format('d M Y') }} s/d {{ $b->trip->return_date ? $b->trip->return_date->format('d M Y') : '-' }}
                                    </p>
                                @else
                                    <p class="text-[11px] text-slate-400 italic mt-0.5">Jadwal trip mandiri</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-[200px]">
                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-700">
                                        {{ $b->pax }} Pax
                                    </span>
                                </div>
                                <div class="mt-1 space-y-0.5 text-[11px] text-slate-600">
                                    @foreach(array_slice($participants, 0, 2) as $pIdx => $pItem)
                                        <p class="truncate">• {{ $pItem['name'] ?? 'Peserta' }} @if($pIdx === 0)<span class="text-[9px] text-teal-700 font-bold">(PJ)</span>@endif</p>
                                    @endforeach
                                    @if(count($participants) > 2)
                                        <p class="text-[10px] text-slate-400 italic">+ {{ count($participants) - 2 }} peserta lainnya</p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-[180px]">
                                <p class="font-bold text-slate-800 text-[11px] truncate">{{ $b->pickup_location ?: 'Titik kumpul resmi' }}</p>
                                @if($b->pickup_details)
                                    <p class="line-clamp-1 text-[10px] text-slate-400">{{ $b->pickup_details }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-extrabold text-slate-900">Rp {{ number_format($b->total_price, 0, ',', '.') }}</p>
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-extrabold ring-1 {{ $payCls }}">
                                        {{ $payLabel }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($b->is_checked_in)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-teal-800 ring-1 ring-teal-600/20">
                                        ✓ Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">
                                        Belum
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold ring-1 {{ $stCls }}">
                                    {{ $stLabel }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($b->trip_id)
                                    <a 
                                        href="{{ route('admin.manifest.index', ['trip_id' => $b->trip_id, 'q' => $b->booking_code]) }}" 
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-[11px] font-bold text-teal-800 hover:bg-teal-50 hover:border-teal-500 shadow-2xs transition"
                                        title="Buka manifes penumpang trip ini"
                                    >
                                        Manifes
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                Customer ini belum memiliki riwayat reservasi atau pemesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BOTTOM SECTION: AKTIVITAS TIMELINE & KONTROL FORM --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- REKAM JEJAK AKTIVITAS (ACTIVITY TIMELINE) --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Rekam Jejak Aktivitas Customer</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Log audit kronologis dari aktivitas pendaftaran, login, reservasi, pembayaran, dan ulasan.
                </p>
            </div>

            <div class="space-y-4 max-h-[460px] overflow-y-auto pr-2">
                @forelse($activities as $act)
                    <div class="relative pl-6 pb-4 border-l-2 border-slate-100 last:border-transparent last:pb-0">
                        <span class="absolute -left-1.5 top-0.5 w-3 h-3 rounded-full bg-teal-600 ring-4 ring-white"></span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-extrabold text-xs text-slate-900">{{ $act->title }}</span>
                            <span class="text-[10px] text-slate-400">{{ $act->date->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">{{ $act->description }}</p>
                        <div class="mt-1.5">
                            <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-extrabold ring-1 {{ $act->badge_color }}">
                                {{ $act->badge }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada catatan aktivitas.</p>
                @endforelse
            </div>
        </div>

        {{-- KONTROL DATA PROFIL & KEAMANAN TERPUSAT --}}
        <div class="space-y-6">

            {{-- Form Edit Data Customer --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Kontrol Data Profil Customer</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Koreksi informasi nama, email, atau nomor WhatsApp pelanggan jika terdapat kesalahan kontak.
                    </p>
                </div>

                <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Customer</label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name', $customer->name) }}" 
                            required 
                            class="w-full text-xs rounded-xl border border-slate-200 p-2.5 text-slate-800 focus:outline-none focus:border-teal-600"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                            <input 
                                type="email" 
                                name="email" 
                                value="{{ old('email', $customer->email) }}" 
                                required 
                                class="w-full text-xs rounded-xl border border-slate-200 p-2.5 text-slate-800 font-mono focus:outline-none focus:border-teal-600"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp</label>
                            <input 
                                type="text" 
                                name="whatsapp" 
                                value="{{ old('whatsapp', $customer->whatsapp) }}" 
                                placeholder="628123456789" 
                                class="w-full text-xs rounded-xl border border-slate-200 p-2.5 text-slate-800 font-mono focus:outline-none focus:border-teal-600"
                            >
                        </div>
                    </div>

                    {{-- Nomor Identitas Resmi (Read-Only untuk Admin) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Nomor Identitas Resmi (KTP / Paspor)</label>
                            <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">Hanya Dibaca</span>
                        </div>
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-mono text-slate-700">
                            {{ $customer->identity_number ?: 'Belum diisi oleh customer (Menunggu pengisian mandiri)' }}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 italic">
                            *Admin tidak diperkenankan mengubah nomor identitas secara manual. Data ini murni diinput mandiri oleh customer yang bersangkutan.
                        </p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-teal-800 text-white rounded-xl text-xs font-bold hover:bg-teal-900 transition shadow-2xs">
                            Simpan Perubahan Kontak
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card Kontrol Keamanan (Blacklist & Hapus Akun) --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Kontrol Keamanan &amp; Sanksi Akun</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tindakan administratif terhadap akun customer yang bermasalah, indikasi penipuan, atau pelanggaran.
                    </p>
                </div>

                {{-- Blacklist Section --}}
                <div class="p-4 rounded-xl {{ $customer->is_blacklisted ? 'bg-emerald-50/60 border border-emerald-200' : 'bg-rose-50/60 border border-rose-200' }}">
                    <h4 class="text-xs font-extrabold {{ $customer->is_blacklisted ? 'text-emerald-900' : 'text-rose-900' }}">
                        {{ $customer->is_blacklisted ? 'Pulihkan Akses Akun (Un-blacklist)' : 'Tutup Akses Akun (Blacklist)' }}
                    </h4>
                    <p class="text-[11px] {{ $customer->is_blacklisted ? 'text-emerald-700' : 'text-rose-700' }} mt-0.5">
                        {{ $customer->is_blacklisted 
                            ? 'Membuka kembali sanksi pembekuan akun. Customer dapat kembali login dan melakukan pemesanan tiket wisata.'
                            : 'Mematikan akses login bagi customer ini secara permanen hingga status blacklist dicabut oleh admin.' 
                        }}
                    </p>

                    @if($customer->is_blacklisted)
                        <form action="{{ route('admin.customers.toggle-blacklist', $customer) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800 transition shadow-2xs">
                                Buka Sanksi Blacklist &amp; Aktifkan Akun
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.customers.toggle-blacklist', $customer) }}" method="POST" class="mt-3 space-y-2">
                            @csrf
                            <input 
                                type="text" 
                                name="blacklist_reason" 
                                required 
                                placeholder="Wajib isi alasan blacklist (cth: Bukti transfer palsu)" 
                                class="w-full text-xs rounded-xl border border-rose-200 p-2.5 text-slate-800 bg-white focus:outline-none focus:border-rose-600"
                            >
                            <button type="submit" class="px-4 py-2 bg-rose-700 text-white rounded-xl text-xs font-bold hover:bg-rose-800 transition shadow-2xs">
                                Terapkan Sanksi Blacklist
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Hapus Akun Section --}}
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-extrabold text-slate-900">Hapus Akun Permanen</p>
                        <p class="text-[11px] text-slate-400">Menghapus data akun customer dari database secara permanen.</p>
                    </div>
                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun {{ $customer->name }} secara permanen? Data riwayat pemesanan lama akan dipertahankan tanpa akun.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-2 border border-rose-200 text-rose-700 rounded-xl text-xs font-bold hover:bg-rose-50 transition cursor-pointer">
                            Hapus Akun
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection


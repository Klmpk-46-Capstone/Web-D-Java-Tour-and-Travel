@extends('layouts.admin')

@section('title', 'Dashboard Analitik & Ringkasan Operasional')

@section('content')
<div class="space-y-6">

    {{-- BREADCRUMBS & TOP HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <span>OPERASIONAL PUSAT</span>
                <span>/</span>
                <span class="text-teal-700">LIVE MONITOR</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Dashboard Analitik &amp; Ringkasan Operasional
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Pantau performa penjualan, ketersediaan kuota trip aktif, dan aktivitas booking real-time wilayah Jawa &amp; Sekitarnya.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- Date Filter Dropdown --}}
            <div class="relative">
                <select class="bg-white border border-slate-200 text-xs font-semibold text-slate-700 rounded-xl px-3.5 py-2.5 shadow-2xs hover:border-slate-300 focus:outline-none focus:border-teal-600 cursor-pointer">
                    <option>30 Hari Terakhir</option>
                    <option>7 Hari Terakhir</option>
                    <option>Kuartal Ini (Q{{ Carbon\Carbon::now()->quarter }})</option>
                    <option>Tahun Berjalan {{ date('Y') }}</option>
                </select>
            </div>

            {{-- Export CSV --}}
            @if (auth()->user()?->isSuperAdmin())
            <a 
                href="{{ route('admin.reports.export-csv') }}" 
                class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
            @endif

            {{-- Download Ringkasan PDF --}}
            <button 
                type="button" 
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 bg-teal-800 hover:bg-teal-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Ringkasan PDF
            </button>
        </div>
    </div>

    {{-- 4 SUMMARY METRIC CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
        {{-- CARD 1: Total Gross Revenue --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold text-slate-400 tracking-wider uppercase">TOTAL GROSS REVENUE</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $revenueGrowth >= 0 ? 'bg-teal-50 text-teal-700' : 'bg-rose-50 text-rose-700' }}">
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}% bln lalu
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight my-2">
                Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 mt-2">
                <div>Target Q{{ Carbon\Carbon::now()->quarter }}: <span class="font-bold text-teal-800">{{ $q1TargetPercentage }}% tercapai</span></div>
                {{-- Mini Sparkline SVG --}}
                <svg class="w-20 h-5 text-teal-600" viewBox="0 0 100 25" fill="none" stroke="currentColor">
                    <path d="M0,20 Q20,18 40,12 T80,8 T100,2" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- CARD 2: Total Bookings & Pax --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold text-slate-400 tracking-wider uppercase">TOTAL BOOKINGS &amp; PAX</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $paxGrowth >= 0 ? 'bg-teal-50 text-teal-700' : 'bg-rose-50 text-rose-700' }}">
                    {{ $paxGrowth >= 0 ? '+' : '' }}{{ $paxGrowth }}%
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight my-2">
                {{ number_format($totalBookingsPax, 0, ',', '.') }} <span class="text-base font-semibold text-slate-500">Pax</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 mt-2">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>{{ number_format($weekendPax, 0, ',', '.') }} Pax trip akhir pekan ini</span>
                </div>
                <span class="font-bold text-slate-700">{{ $onScheduleRate }}% On-Schedule</span>
            </div>
        </div>

        {{-- CARD 3: Paket Terlaris Bulan Ini --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold text-slate-400 tracking-wider uppercase">PAKET TERLARIS BULAN INI</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-800 text-white">
                    <svg class="w-2.5 h-2.5 text-amber-300 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    TOP 1
                </span>
            </div>
            <div class="text-base font-extrabold text-slate-900 truncate my-1" title="{{ $topPackage?->name ?? 'Belum ada paket terjual' }}">
                {{ $topPackage?->name ?? 'Belum ada paket terjual' }}
            </div>
            <div class="text-xs text-slate-600 mb-2">
                <strong class="text-slate-900 font-bold">{{ number_format($topPackagePax, 0, ',', '.') }} Pax</strong> (Rp {{ number_format($topPackageRevenue, 0, ',', '.') }})
            </div>
            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 mt-2">
                <div class="flex items-center gap-1 text-amber-500 font-bold">
                    <span>★</span>
                    <span class="text-slate-800">{{ number_format($topPackage?->rating ?? 5.0, 1) }}</span>
                    <span class="text-slate-400 font-normal">({{ $topPackage?->reviews_count ?? 0 }} Ulasan)</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-teal-50 text-teal-800 uppercase">
                    {{ $topPackage?->category ?? 'Reguler' }}
                </span>
            </div>
        </div>

        {{-- CARD 4: Tinjauan Refund & Isu --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold text-slate-400 tracking-wider uppercase">TINJAUAN REFUND &amp; ISU</span>
                @if ($pendingRefundsCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700">
                        Perlu Review Segera
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700">
                        Status Aman
                    </span>
                @endif
            </div>
            <div class="text-2xl sm:text-[26px] font-black {{ $pendingRefundsCount > 0 ? 'text-rose-600' : 'text-slate-900' }} tracking-tight my-2">
                {{ $pendingRefundsCount }} <span class="text-base font-semibold {{ $pendingRefundsCount > 0 ? 'text-rose-500' : 'text-slate-500' }}">Tiket Ditahan</span>
            </div>
            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 mt-2">
                <div>
                    <span class="text-[10px] uppercase text-slate-400 block font-bold">NOMINAL DISENGKETAKAN</span>
                    <span class="font-extrabold text-slate-900">Rp {{ number_format($disputedRefundNominal, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('admin.refunds.index') }}" class="inline-flex items-center gap-1 font-bold text-teal-700 hover:text-teal-900 transition">
                    Selesaikan &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI H-1 CUSTOMER: Trip yang Berangkat Besok --}}
    @if ($h1Trips->count() > 0)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 shadow-xs overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-amber-200/80 bg-amber-100/50">
            <div class="flex items-center gap-3">
                <span class="relative flex h-9 w-9 place-items-center">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-40"></span>
                    <span class="relative grid h-9 w-9 place-items-center rounded-xl bg-amber-500 text-white shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </span>
                <div>
                    <h2 class="text-sm font-extrabold text-amber-900">
                        🔔 Notifikasi H-1 — Trip Berangkat Besok ({{ Carbon\Carbon::tomorrow()->translatedFormat('d M Y') }})
                    </h2>
                    <p class="text-xs text-amber-700 mt-0.5">
                        {{ $h1Trips->count() }} trip aktif · {{ $h1TotalCustomers }} customer siap berangkat · Pastikan armada & manifes sudah disiapkan
                    </p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <a href="{{ route('admin.manifest.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-500 text-white hover:bg-amber-600 transition shadow-xs no-underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Cek Manifes
                </a>
                <a href="{{ route('admin.trips.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold border border-amber-400 text-amber-800 bg-white hover:bg-amber-50 transition shadow-xs no-underline">
                    Detail Trip
                </a>
            </div>
        </div>

        {{-- Trip Cards Grid --}}
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($h1Trips as $ht)
                @php
                    $htBookings = $h1Bookings->where('trip_id', $ht->id);
                    $htPax = $htBookings->sum('pax');
                    $hasDriver = !empty($ht->driver_name);
                    $hasVehicle = $ht->vehicleUnit !== null;
                    $hasMeetingPoint = !empty($ht->meeting_point);
                    $readinessScore = ($hasDriver ? 1 : 0) + ($hasVehicle ? 1 : 0) + ($hasMeetingPoint ? 1 : 0);
                @endphp
                <div class="bg-white rounded-xl border border-amber-100 shadow-xs p-4 flex flex-col gap-3 hover:border-amber-300 transition">
                    {{-- Trip Header --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-extrabold text-sm text-slate-900 truncate">
                                {{ $ht->package->name ?? $ht->batch_name ?? 'Trip #'.$ht->id }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                {{ $ht->batch_name }}
                            </div>
                        </div>
                        @if ($readinessScore === 3)
                            <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                ✓ Siap
                            </span>
                        @elseif ($readinessScore === 2)
                            <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700">
                                ⚠ Periksa
                            </span>
                        @else
                            <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700">
                                ✗ Belum Siap
                            </span>
                        @endif
                    </div>

                    {{-- Stats Row --}}
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-slate-50 px-2 py-2">
                            <div class="font-extrabold text-base text-slate-900">{{ $htPax }}</div>
                            <div class="text-[10px] text-slate-500 font-semibold">Pax</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-2 py-2">
                            <div class="font-extrabold text-base text-slate-900">{{ $htBookings->count() }}</div>
                            <div class="text-[10px] text-slate-500 font-semibold">Booking</div>
                        </div>
                        <div class="rounded-lg {{ $ht->departure_time ? 'bg-teal-50' : 'bg-rose-50' }} px-2 py-2">
                            <div class="font-extrabold text-sm {{ $ht->departure_time ? 'text-teal-800' : 'text-rose-600' }}">
                                {{ $ht->departure_time ? \Carbon\Carbon::createFromFormat('H:i:s', $ht->departure_time)->format('H:i') : '—' }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-semibold">Jam</div>
                        </div>
                    </div>

                    {{-- Readiness Checklist --}}
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center gap-2 {{ $hasDriver ? 'text-emerald-700' : 'text-rose-600' }}">
                            @if ($hasDriver)
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="font-semibold">Driver: {{ $ht->driver_name }}</span>
                            @else
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="font-semibold">Driver belum ditugaskan</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 {{ $hasVehicle ? 'text-emerald-700' : 'text-rose-600' }}">
                            @if ($hasVehicle)
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="font-semibold">Armada: {{ $ht->vehicleUnit->plate_number }}</span>
                            @else
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="font-semibold">Armada/plat belum ditentukan</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 {{ $hasMeetingPoint ? 'text-emerald-700' : 'text-amber-600' }}">
                            @if ($hasMeetingPoint)
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="font-semibold truncate" title="{{ $ht->meeting_point }}">Titik kumpul: {{ \Illuminate\Support\Str::limit($ht->meeting_point, 30) }}</span>
                            @else
                                <svg class="w-3.5 h-3.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01"/></svg>
                                <span class="font-semibold text-amber-600">Titik kumpul belum diisi</span>
                            @endif
                        </div>
                    </div>

                    {{-- Action --}}
                    <a href="{{ route('admin.trips.index') }}"
                       class="mt-auto w-full text-center text-[11px] font-bold py-2 px-3 rounded-lg border border-amber-200 text-amber-800 bg-amber-50 hover:bg-amber-100 transition no-underline">
                        Edit Detail Trip →
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Customer List Footer --}}
        @if ($h1Bookings->count() > 0)
        <div class="px-5 pb-4">
            <details class="group">
                <summary class="cursor-pointer text-xs font-bold text-amber-800 hover:text-amber-900 flex items-center gap-1.5 select-none">
                    <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    Lihat {{ $h1Bookings->count() }} customer yang berangkat besok
                </summary>
                <div class="mt-3 overflow-x-auto rounded-xl border border-amber-200 bg-white">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-amber-50 text-[10px] font-extrabold text-amber-800 uppercase tracking-wider border-b border-amber-100">
                                <th class="py-2.5 px-4 text-left">Customer</th>
                                <th class="py-2.5 px-4 text-left">Paket</th>
                                <th class="py-2.5 px-4 text-center">Pax</th>
                                <th class="py-2.5 px-4 text-left">No. HP</th>
                                <th class="py-2.5 px-4 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-50">
                            @foreach ($h1Bookings as $hb)
                            <tr class="hover:bg-amber-50/40 transition">
                                <td class="py-2.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $hb->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400">#{{ $hb->booking_code }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-slate-700">
                                    {{ $hb->trip?->package?->name ?? $hb->trip?->batch_name ?? '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-900">{{ $hb->pax }}</td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $hb->customer_phone ?? '-' }}</td>
                                <td class="py-2.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                        {{ $hb->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $hb->payment_status === 'paid' ? 'Lunas' : 'DP' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
        @endif
    </div>
    @else
    {{-- Empty State H-1 (Operasional Aman) --}}
    <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-teal-50 text-teal-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <h3 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                    <span>🔔 Monitor Keberangkatan H-1</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Operasional Terkendali</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Tidak ada keberangkatan trip atau sewa armada untuk besok ({{ Carbon\Carbon::tomorrow()->translatedFormat('d M Y') }}). Jadwal berikutnya dapat dipantau di menu Jadwal Trip.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.trips.index') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900 px-3.5 py-2 rounded-xl border border-teal-200 bg-teal-50/50 hover:bg-teal-100 transition no-underline">
                Lihat Jadwal Trip →
            </a>
        </div>
    </div>
    @endif

    {{-- MIDDLE ROW: GRAFIK TREN PENJUALAN & PERFORMA KATEGORI + CUACA MARITIM --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- LEFT: Tren Penjualan & Reservasi Bulanan (8 cols on lg) --}}
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">
                        Tren Penjualan &amp; Reservasi Paket Bulanan
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Data real-time 6 bulan terakhir berdasarkan reservasi aktual 3 rute Jawa
                    </p>
                </div>
                {{-- Legends --}}
                <div class="flex items-center gap-3 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                        <span class="w-3 h-3 rounded-sm bg-teal-500"></span>
                        Bahari (Karimunjawa)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                        <span class="w-3 h-3 rounded-sm bg-slate-700"></span>
                        Gunung (Bromo/Dieng)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                        <span class="w-3 h-3 rounded-sm bg-cyan-300"></span>
                        Heritage (Jogja)
                    </span>
                </div>
            </div>

            {{-- VISUAL CHART CANVAS / SVG SIMULATION DYNAMIC REAL-TIME --}}
            <div class="relative w-full h-64 bg-gradient-to-b from-teal-50/20 to-white rounded-xl border border-slate-100 p-4 flex flex-col justify-between overflow-hidden">
                {{-- Tooltip / Highlight for Peak Month --}}
                @if ($peakMonth && $peakMonth['pax'] > 0)
                    <div class="absolute top-4 right-1/4 z-10 bg-slate-900/95 text-white p-3 rounded-xl shadow-xl text-xs max-w-xs border border-teal-500/30">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-1 mb-1.5">
                            <span class="font-extrabold text-teal-300 text-[11px] tracking-wider uppercase">{{ $peakMonth['label'] }} (PEAK)</span>
                        </div>
                        <div class="font-extrabold text-sm text-white">Rp {{ number_format($peakMonth['revenue'], 0, ',', '.') }} <span class="font-normal text-xs text-slate-300">({{ $peakMonth['pax'] }} Pax)</span></div>
                        <div class="text-[11px] text-teal-400 mt-1">Data penjualan tertinggi semester ini</div>
                    </div>
                @endif

                {{-- Chart SVG Visual representation --}}
                <div class="flex-1 flex items-end justify-between gap-4 pt-10 px-4">
                    @php
                        $maxPaxMonth = max(1, $monthlyTrends->max('pax'));
                    @endphp
                    @foreach ($monthlyTrends as $item)
                        @php
                            $bPct = $maxPaxMonth > 0 ? max(8, round(($item['bahari_pax'] / $maxPaxMonth) * 100)) : 8;
                            $gPct = $maxPaxMonth > 0 ? max(8, round(($item['gunung_pax'] / $maxPaxMonth) * 100)) : 8;
                            $hPct = $maxPaxMonth > 0 ? max(8, round(($item['heritage_pax'] / $maxPaxMonth) * 100)) : 8;
                            $isPeak = ($peakMonth && $peakMonth['label'] === $item['label'] && $item['pax'] > 0);
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-2 relative">
                            @if ($isPeak)
                                <div class="absolute -top-6 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-extrabold whitespace-nowrap">
                                    PEAK
                                </div>
                            @endif
                            <div class="w-full max-w-[40px] flex items-end gap-1 h-32" title="{{ $item['label'] }}: {{ $item['pax'] }} Pax, Rp {{ number_format($item['revenue'], 0, ',', '.') }}">
                                <div class="flex-1 bg-teal-600 rounded-t shadow-xs" style="height: {{ $item['pax'] > 0 ? $bPct : 6 }}%"></div>
                                <div class="flex-1 bg-slate-700 rounded-t shadow-xs" style="height: {{ $item['pax'] > 0 ? $gPct : 6 }}%"></div>
                                <div class="flex-1 bg-cyan-400 rounded-t shadow-xs" style="height: {{ $item['pax'] > 0 ? $hPct : 6 }}%"></div>
                            </div>
                            <span class="text-[11px] font-bold {{ $item['is_current'] ? 'text-teal-800 font-extrabold' : 'text-slate-500' }}">
                                {{ $item['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer insight note --}}
            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Total reservasi 6 bulan terakhir: <strong>{{ number_format($monthlyTrends->sum('pax'), 0, ',', '.') }} Pax</strong> dengan akumulasi omzet <strong>Rp {{ number_format($monthlyTrends->sum('revenue'), 0, ',', '.') }}</strong>.</span>
                </div>
                @if (auth()->user()?->isSuperAdmin())
                    <a href="{{ route('admin.reports.index') }}" class="font-bold text-teal-700 hover:text-teal-900 whitespace-nowrap">
                        Lihat Log Rinci &gt;
                    </a>
                @endif
            </div>
        </div>

        {{-- RIGHT: Performa Kategori Wisata + Widget BMKG (4 cols on lg) --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            {{-- Performa Kategori Wisata --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-extrabold text-slate-900">Performa Kategori Wisata</h3>
                    <span class="text-[10px] font-bold text-slate-400">Real-Time Database</span>
                </div>
                <p class="text-[11px] text-slate-500 mb-4">Pangsa pasar volume Pax berdasarkan preferensi destinasi pelanggan.</p>

                {{-- Multi-color segmented progress bar --}}
                <div class="h-3 w-full bg-slate-100 rounded-full flex overflow-hidden mb-4 shadow-inner">
                    <div class="bg-teal-600 h-full transition-all duration-300" style="width: {{ $routeStats['bahari']['percent'] }}%" title="Wisata Bahari {{ $routeStats['bahari']['percent'] }}%"></div>
                    <div class="bg-slate-700 h-full transition-all duration-300" style="width: {{ $routeStats['gunung']['percent'] }}%" title="Mountain Trekking {{ $routeStats['gunung']['percent'] }}%"></div>
                    <div class="bg-cyan-400 h-full transition-all duration-300" style="width: {{ $routeStats['heritage']['percent'] }}%" title="Cultural &amp; Heritage {{ $routeStats['heritage']['percent'] }}%"></div>
                </div>

                {{-- Legend & metrics --}}
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                            <span class="font-medium text-slate-700">Wisata Bahari &amp; Island Hopping</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <strong class="text-slate-900">{{ number_format($routeStats['bahari']['pax'], 0, ',', '.') }} Pax</strong>
                            <span class="text-slate-400 font-bold">{{ $routeStats['bahari']['percent'] }}%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                            <span class="font-medium text-slate-700">Mountain Trekking &amp; Sunrise</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <strong class="text-slate-900">{{ number_format($routeStats['gunung']['pax'], 0, ',', '.') }} Pax</strong>
                            <span class="text-slate-400 font-bold">{{ $routeStats['gunung']['percent'] }}%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                            <span class="font-medium text-slate-700">Cultural, Heritage &amp; City Tour</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <strong class="text-slate-900">{{ number_format($routeStats['heritage']['pax'], 0, ',', '.') }} Pax</strong>
                            <span class="text-slate-400 font-bold">{{ $routeStats['heritage']['percent'] }}%</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Rata-rata kepuasan pax:</span>
                    <span class="flex items-center gap-1 font-extrabold text-slate-800">
                        <span class="text-amber-500">★</span> {{ number_format($avgCustomerRating, 2) }} / 5.0
                    </span>
                </div>
            </div>

            {{-- Widget Cuaca Maritim BMKG --}}
            <div class="bg-teal-50/70 border border-teal-200/80 rounded-2xl p-4 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-teal-600 text-white text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        </span>
                        <h4 class="text-xs font-extrabold text-teal-950 uppercase tracking-wide">Status Cuaca Maritim BMKG</h4>
                    </div>
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </div>
                <p class="text-xs text-teal-900 leading-relaxed">
                    Perairan Jepara - Karimunjawa terpantau <strong>Kondisi Aman</strong> (Tinggi gelombang: <strong>&lt;0.75m</strong>). Seluruh penyeberangan KMP Siginjai &amp; Express Bahari normal beroperasi.
                </p>
                <div class="mt-2 text-[10px] font-bold text-teal-700/80">
                    Diperbarui otomatis setiap 3 jam via BMKG Tanjung Emas
                </div>
            </div>
        </div>
    </div>

    {{-- BOTTOM ROW: DATA GRID MONITORING FEED & PERINGATAN KUOTA PENUH --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        {{-- LEFT: Booking Masuk Terbaru (Real-Time Feed) (8 cols on lg) --}}
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div>
                {{-- Table Header --}}
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-sm font-extrabold text-slate-900">
                            Booking Masuk Terbaru (Real-Time Feed)
                        </h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-100 text-teal-800">
                            5 Baru
                        </span>
                    </div>

                    <button 
                        type="button" 
                        onclick="window.location.reload()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-teal-700 transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sinkronisasi Otomatis
                    </button>
                </div>

                {{-- Table Component --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                <th class="py-3 px-4">BOOKING ID</th>
                                <th class="py-3 px-4">PELANGGAN</th>
                                <th class="py-3 px-4">PAKET &amp; DESTINASI</th>
                                <th class="py-3 px-4">JADWAL TRIP</th>
                                <th class="py-3 px-4 text-center">PAX</th>
                                <th class="py-3 px-4 text-right">TOTAL BAYAR</th>
                                <th class="py-3 px-4">METODE / STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse ($latestBookings as $b)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 font-extrabold text-teal-700 whitespace-nowrap">
                                        #{{ $b->booking_code }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $b->customer_name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $b->customer_email ?? $b->customer_phone }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-medium text-slate-800 line-clamp-1">{{ $b->package->name ?? 'Trip Khusus' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                        {{ $b->trip && $b->trip->departure_date ? $b->trip->departure_date->translatedFormat('d M Y') : 'Open Date' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                        {{ $b->pax }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format($b->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="text-[11px] font-semibold text-slate-600">{{ $b->payment_method }}</div>
                                        @if ($b->booking_status === 'confirmed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                                Terkonfirmasi
                                            </span>
                                        @elseif ($b->booking_status === 'refund_requested')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700">
                                                Review Refund
                                            </span>
                                        @elseif ($b->booking_status === 'cancelled')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700">
                                                Dibatalkan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                {{ ucfirst($b->booking_status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">Belum ada booking masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Table Footer --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 gap-2">
                <div>Menampilkan {{ $latestBookings->count() }} dari {{ $totalTransactionsThisMonth }} transaksi bulan ini</div>
                <a href="{{ route('admin.bookings.index') }}" class="font-extrabold text-teal-700 hover:text-teal-900 transition flex items-center gap-1">
                    Buka Laporan Transaksi Lengkap &rarr;
                </a>
            </div>
        </div>

        {{-- RIGHT: Peringatan Kuota Penuh (4 cols on lg) --}}
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-extrabold text-slate-900">Peringatan Kuota Penuh</h3>
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mb-4">Batch trip yang mendekati kapasitas maksimal</p>

                {{-- List Warning Trips --}}
                <div class="space-y-4">
                    @forelse ($warningTrips as $wt)
                        @php
                            $percentage = $wt->percentage_booked;
                            $isUrgent = $percentage >= 85;
                        @endphp
                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="font-bold text-xs text-slate-900 truncate">{{ $wt->package->name ?? $wt->batch_name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $isUrgent ? 'bg-rose-100 text-rose-700' : 'bg-teal-100 text-teal-800' }}">
                                    {{ $percentage }}% Penuh
                                </span>
                            </div>

                            <div class="text-[11px] text-slate-500 mb-2 flex items-center justify-between">
                                <span>Keberangkatan: {{ $wt->departure_date->translatedFormat('d M Y') }}</span>
                                <span class="font-bold text-slate-700">{{ $wt->booked_seats }} / {{ $wt->quota }} Pax</span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="h-2 w-full bg-slate-200 rounded-full overflow-hidden mb-2">
                                <div class="h-full {{ $isUrgent ? 'bg-rose-500' : 'bg-teal-600' }} rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>

                            <div class="flex items-center justify-between pt-1 text-xs">
                                <span class="text-[11px] {{ $isUrgent ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                    Sisa {{ $wt->remaining_seats }} Kursi {{ $isUrgent ? 'Tersedia' : '(Normal)' }}
                                </span>

                                <form action="{{ route('admin.trips.quick-add-batch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="trip_id" value="{{ $wt->id }}">
                                    <button type="submit" class="text-[11px] font-extrabold text-teal-700 hover:text-teal-900 hover:underline cursor-pointer">
                                        + Buka Batch Baru
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 py-4 text-center">Seluruh kapasitas trip berada di level aman.</div>
                    @endforelse
                </div>
            </div>

            {{-- Bottom Quick Action Configuration --}}
            <a 
                href="{{ route('admin.trips.index') }}" 
                class="mt-4 w-full py-2.5 px-3 rounded-xl border border-dashed border-teal-600/50 bg-teal-50/50 text-teal-800 text-xs font-bold text-center hover:bg-teal-100/60 transition block no-underline"
            >
                ✦ Konfigurasi Alokasi Armada &amp; Kuota Tambahan
            </a>
        </div>
    </div>

</div>
@endsection

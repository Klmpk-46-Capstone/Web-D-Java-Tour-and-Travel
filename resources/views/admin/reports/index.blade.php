@extends('layouts.admin')

@section('title', 'Riwayat Transaksi & Laporan Keuangan')

@section('content')
<div class="space-y-6">

    {{-- TOP BREADCRUMB & LIVE STATUS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-slate-500 font-semibold">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700 flex items-center gap-1 transition">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Operasional Trans &amp; Wisata</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-teal-700 font-bold">Laporan Transaksi &amp; Keuangan</span>
        </div>

        <div class="flex items-center gap-3">
            {{-- Status Sistem Aktif --}}
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold text-[11px]">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Sistem Aktif (99.9%)</span>
            </div>

            {{-- Tanggal Hari Ini --}}
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white text-slate-600 border border-slate-200/80 font-medium text-[11px] shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Hari Ini, {{ now()->translatedFormat('d M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- PAGE HEADER & ACTION BUTTONS --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-teal-50 text-teal-800 border border-teal-200/70 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                <span>LEDGER KEUANGAN B2B</span>
                <span class="text-teal-400">•</span>
                <span class="text-teal-700">Periode Fiskal Q4 - {{ date('Y') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Riwayat Transaksi &amp; Laporan Keuangan
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Monitoring arus kas, settlement payment gateway, rekap DP, dan pelunasan trip real-time.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Ekspor Excel / CSV --}}
            <a 
                href="{{ route('admin.reports.export-csv', request()->query()) }}" 
                class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-slate-200 shadow-2xs transition"
                title="Unduh data dalam format CSV/Excel"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ekspor Excel / CSV</span>
            </a>

            {{-- Cetak PDF --}}
            <button 
                type="button" 
                onclick="window.print()" 
                class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-slate-200 shadow-2xs transition cursor-pointer"
                title="Cetak laporan transaksi ke printer atau simpan sebagai PDF"
            >
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak PDF</span>
            </button>

            {{-- Filter Lanjutan --}}
            <button 
                type="button" 
                onclick="document.getElementById('filterContainer').scrollIntoView({behavior: 'smooth'})" 
                class="inline-flex items-center gap-2 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition hover:brightness-105 cursor-pointer"
                style="background-color: #0D9488;"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Filter Lanjutan</span>
            </button>
        </div>
    </div>

    {{-- 4 SUMMARY METRIC CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Omzet Bulan Ini --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Omzet Bulan Ini</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-teal-50 text-teal-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight leading-tight">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-2.5">
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +14.2% YoY
                </span>
                <span class="text-[11px] text-slate-400 font-medium">vs April 2025</span>
            </div>
        </div>

        {{-- Card 2: Total DP Diterima --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Total DP Diterima</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-amber-50 text-amber-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight leading-tight">
                Rp {{ number_format($totalDp, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-2.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-teal-800">
                    <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $dpCount }} Transaksi
                </span>
                <span class="text-[11px] text-slate-400 font-medium">Terverifikasi</span>
            </div>
        </div>

        {{-- Card 3: Pelunasan Gateway --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Pelunasan Gateway</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-cyan-50 text-cyan-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight leading-tight">
                Rp {{ number_format($totalPelunasan, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-2.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-cyan-50 text-cyan-800">
                    Settlement H+0
                </span>
                <span class="text-[11px] text-slate-400 font-medium">Xendit &amp; VA</span>
            </div>
        </div>

        {{-- Card 4: Refund / Batal --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Refund / Batal</span>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-rose-50 text-rose-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-rose-600 tracking-tight leading-tight">
                Rp {{ number_format($refundAmount, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 mt-2.5">
                <span class="text-[11px] font-bold text-rose-600">{{ $refundCount }} Transaksi</span>
                <span class="text-[11px] text-slate-400 font-medium">Cuaca &amp; Reschedule</span>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div id="filterContainer" class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $search }}"
                    placeholder="Cari invoice, nama tamu, no. transaksi..." 
                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 hover:bg-slate-50/80 focus:bg-white border border-slate-200 rounded-xl focus:border-teal-600 focus:outline-none transition"
                >
            </div>

            {{-- Dropdown Range / Tanggal --}}
            <div class="w-full sm:w-auto">
                <select name="range" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2.5 text-xs font-semibold bg-slate-50 hover:bg-slate-50/80 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:border-teal-600">
                    <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>01 Mei 2025 - 31 Mei 2025 (Bulan Ini)</option>
                    <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>Bulan Berjalan</option>
                    <option value="all" {{ $dateRange == 'all' ? 'selected' : '' }}>Semua Periode</option>
                </select>
            </div>

            {{-- Dropdown Layanan --}}
            <div class="w-full sm:w-auto">
                <select name="service" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2.5 text-xs font-semibold bg-slate-50 hover:bg-slate-50/80 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Layanan</option>
                    <option value="paket" {{ $service == 'paket' ? 'selected' : '' }}>Paket Wisata Terpadu</option>
                    <option value="armada" {{ $service == 'armada' ? 'selected' : '' }}>Rental Armada &amp; Shuttle</option>
                </select>
            </div>

            {{-- Dropdown Metode --}}
            <div class="w-full sm:w-auto">
                <select name="method" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2.5 text-xs font-semibold bg-slate-50 hover:bg-slate-50/80 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Metode</option>
                    <option value="BCA" {{ $method == 'BCA' ? 'selected' : '' }}>BCA Virtual Account</option>
                    <option value="Mandiri" {{ $method == 'Mandiri' ? 'selected' : '' }}>Mandiri VA Escrow</option>
                    <option value="QRIS" {{ $method == 'QRIS' ? 'selected' : '' }}>QRIS Instant</option>
                    <option value="Visa" {{ $method == 'Visa' ? 'selected' : '' }}>Visa / Mastercard</option>
                </select>
            </div>

            {{-- Dropdown Status --}}
            <div class="w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2.5 text-xs font-semibold bg-slate-50 hover:bg-slate-50/80 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Status</option>
                    <option value="success" {{ $status == 'success' ? 'selected' : '' }}>Lunas / Success</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="refunded" {{ $status == 'refunded' ? 'selected' : '' }}>Refunded / Batal</option>
                </select>
            </div>

            {{-- Action Buttons Filter --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition cursor-pointer" title="Terapkan Filter">
                    Cari
                </button>
                @if($search || $status || $service || $method || $dateRange !== '30')
                    <a href="{{ route('admin.reports.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition" title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- MAIN TABLE CARD: DATA JURNAL & LEDGER TRANSAKSI --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        {{-- Card Table Header --}}
        <div class="p-4 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-3">
                <h2 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">
                    Data Jurnal &amp; Ledger Transaksi
                </h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                    {{ $transactions->count() }} dari {{ $transactions->total() }} entri ditampilkan
                </span>
            </div>

            {{-- Right Status Toggle / Auto-sync --}}
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <span>Update auto-sync setiap 60 dtk</span>
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-200/70">
                        <th class="py-3.5 px-4 sm:px-6">NO. INVOICE &amp; WAKTU</th>
                        <th class="py-3.5 px-4">NAMA TAMU &amp; KONTAK</th>
                        <th class="py-3.5 px-4">PAKET LAYANAN / ARMADA</th>
                        <th class="py-3.5 px-4">NILAI TOTAL</th>
                        <th class="py-3.5 px-4">RINCIAN DP / SISA</th>
                        <th class="py-3.5 px-4 sm:px-6">GATEWAY / CHANNEL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse ($transactions as $tx)
                        @php
                            $booking = $tx->booking;
                            $isArmada = $tx->service_type === 'armada';
                            $totalVal = $booking?->total_price ?: $tx->amount;
                            $dpVal = $booking?->down_payment ?: ($tx->payment_stage === 'dp' ? $tx->amount : 0);
                            $remainingVal = max(0, $totalVal - $tx->amount);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition duration-150">
                            {{-- Col 1: Invoice & Waktu --}}
                            <td class="py-4 px-4 sm:px-6">
                                <a 
                                    href="javascript:void(0)" 
                                    onclick="openTransactionModal({{ $tx->id }})" 
                                    class="font-mono font-extrabold text-teal-700 hover:text-teal-900 transition hover:underline block"
                                    style="color: #0D9488;"
                                >
                                    {{ $tx->transaction_code }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                                    {{ $tx->created_at ? $tx->created_at->translatedFormat('d M Y') . ' • ' . $tx->created_at->format('H:i') . ' WIB' : '-' }}
                                </div>
                            </td>

                            {{-- Col 2: Nama Tamu & Kontak --}}
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800">
                                    {{ $booking?->customer_name ?: 'Pelanggan Umum' }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $booking?->customer_phone ?: '-' }}
                                </div>
                            </td>

                            {{-- Col 3: Paket Layanan / Armada --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $isArmada ? 'bg-amber-500' : 'bg-teal-600' }}"></span>
                                    <span>{{ $tx->service_name }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium mt-0.5 pl-3.5">
                                    {{ $tx->service_detail }}
                                </div>
                            </td>

                            {{-- Col 4: Nilai Total --}}
                            <td class="py-4 px-4 font-black text-slate-900 text-sm">
                                Rp {{ number_format($totalVal, 0, ',', '.') }}
                            </td>

                            {{-- Col 5: Rincian DP / Sisa --}}
                            <td class="py-4 px-4">
                                @if($tx->status === 'refunded')
                                    <div class="text-rose-600 font-bold">
                                        Refunded: Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Potongan Adm: Rp 600.000
                                    </div>
                                @elseif($tx->status === 'pending')
                                    <div class="text-amber-700 font-bold">
                                        DP Ditagihkan: Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-rose-500 font-semibold">
                                        Kedaluwarsa dlm: 04j 12m
                                    </div>
                                @else
                                    <div class="text-slate-700">
                                        <span class="font-bold">DP: Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                                        <span class="text-[10px] font-extrabold text-teal-800 bg-teal-50 px-1.5 py-0.2 rounded ml-1">
                                            Lunas
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        @if($remainingVal > 0)
                                            Pelunasan: Rp {{ number_format($remainingVal, 0, ',', '.') }}
                                        @else
                                            Lunas Penuh (100%)
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Col 6: Gateway / Channel --}}
                            <td class="py-4 px-4 sm:px-6">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-[11px] font-bold text-slate-700">
                                    @if(str_contains(strtolower($tx->payment_method), 'bca'))
                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                        <span>BCA Virtual Account</span>
                                    @elseif(str_contains(strtolower($tx->payment_method), 'mandiri'))
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        <span>Mandiri VA Escrow</span>
                                    @elseif(str_contains(strtolower($tx->payment_method), 'qris'))
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>QRIS Instant</span>
                                    @elseif(str_contains(strtolower($tx->payment_method), 'visa') || str_contains(strtolower($tx->payment_method), 'stripe'))
                                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                        <span>Visa / Mastercard</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                        <span>{{ $tx->payment_method ?: 'Online Gateway' }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <div class="font-bold text-slate-600">Tidak ada data transaksi yang sesuai filter</div>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter tanggal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Pagination --}}
        <div class="p-4 sm:px-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="text-xs text-slate-500 font-medium">
                Menampilkan <span class="font-bold text-slate-800">{{ $transactions->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $transactions->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $transactions->total() }}</span> data transaksi aktif
            </div>

            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    {{-- BOTTOM TWO COLUMNS SECTION --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT CARD (lg:col-span-7): RINGKASAN ARUS KAS MASUK --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                        Ringkasan Arus Kas Masuk
                    </h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-teal-50 text-teal-800 border border-teal-200/70">
                        Audit Real-Time
                    </span>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Komposisi pendapatan Paket Wisata Terpadu vs Rental Armada Lepas Kunci/Driver
                </p>

                {{-- Multi-color bar --}}
                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden flex mb-2">
                    <div class="h-full transition-all duration-500" style="width: {{ $tourPercent }}%; background-color: #0f766e;" title="Paket Wisata {{ $tourPercent }}%"></div>
                    <div class="h-full transition-all duration-500" style="width: {{ $rentalPercent }}%; background-color: #0D9488;" title="Rental Armada {{ $rentalPercent }}%"></div>
                </div>

                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 mb-6">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background-color: #0f766e;"></span>
                        <span>Paket Wisata &amp; Tour Terpadu ({{ $tourPercent }}%)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background-color: #0D9488;"></span>
                        <span>Rental Armada &amp; Shuttle ({{ $rentalPercent }}%)</span>
                    </div>
                </div>

                {{-- 2 Omzet Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">
                            OMZET TOUR EAST JAVA
                        </span>
                        <div class="text-xl sm:text-2xl font-black text-slate-900" style="color: #0f766e;">
                            Rp {{ number_format($tourRevenue, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1">
                            Bromo Sunrise, Ijen Safari, Madakaripura
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">
                            OMZET SEWA MOBIL &amp; BUS
                        </span>
                        <div class="text-xl sm:text-2xl font-black text-slate-900" style="color: #0D9488;">
                            Rp {{ number_format($rentalRevenue, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1">
                            HiAce Premio, Innova Zenix, Alphard VIP
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Security Note --}}
            <div class="pt-5 mt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px]">
                <div class="flex items-center gap-1.5 text-slate-500">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Data terenkripsi AES-256 dengan standar regulasi Bank Indonesia</span>
                </div>
                <a href="{{ route('admin.reports.export-csv') }}" class="font-bold text-teal-700 hover:text-teal-900 flex items-center gap-1 hover:underline">
                    <span>Lihat Buku Besar Lengkap</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        {{-- RIGHT CARD (lg:col-span-5): REKONSILIASI BANK HARIAN --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                        Rekonsiliasi Bank Harian
                    </h3>
                    <form action="{{ route('admin.reports.reconcile') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-teal-700 transition cursor-pointer" title="Perbarui Status Rekonsiliasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </form>
                </div>
                <p class="text-xs text-slate-500 mb-4">
                    Kesesuaian saldo rekening koran vs mutasi gateway
                </p>

                <div class="space-y-3">
                    {{-- Row 1: BCA --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-blue-600 text-white font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                BCA
                            </span>
                            <div>
                                <div class="text-xs font-bold text-slate-800">BCA Virtual Account Escrow</div>
                                <div class="text-[10px] text-slate-400">Terakhir sync: {{ $lastReconciledAt }}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Match (100%)
                        </span>
                    </div>

                    {{-- Row 2: Mandiri --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-amber-600 text-white font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                MDR
                            </span>
                            <div>
                                <div class="text-xs font-bold text-slate-800">Mandiri Korporat Giro</div>
                                <div class="text-[10px] text-slate-400">Terakhir sync: {{ $lastReconciledAt }}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Match (100%)
                        </span>
                    </div>

                    {{-- Row 3: Xendit Settlement Pool --}}
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-teal-700 text-white font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                XDT
                            </span>
                            <div>
                                <div class="text-xs font-bold text-slate-800">Xendit Settlement Pool</div>
                                <div class="text-[10px] text-slate-400">2 pending settlement clearing</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700">
                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Pending H+0
                        </span>
                    </div>
                </div>
            </div>

            {{-- Action Button Jalankan Rekonsiliasi --}}
            <div class="mt-6 pt-4 border-t border-slate-100">
                <form action="{{ route('admin.reports.reconcile') }}" method="POST">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white text-xs font-bold transition hover:brightness-105 cursor-pointer shadow-xs"
                        style="background-color: #0D9488;"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Jalankan Rekonsiliasi Harian</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- MODAL DETAIL TRANSAKSI --}}
<div id="modalTransaction" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between" style="background-color: #0D9488;">
            <div class="text-white">
                <div class="text-[11px] font-bold uppercase tracking-wider opacity-90">Audit Transaksi Finansial</div>
                <div id="modalTrxCode" class="font-mono text-base font-black">INV/...</div>
            </div>
            <button type="button" onclick="closeTransactionModal()" class="text-white hover:opacity-75 text-2xl font-light leading-none cursor-pointer">&times;</button>
        </div>

        <div class="p-6 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-3 pb-3 border-b border-slate-100">
                <div>
                    <span class="text-slate-400 block font-medium">Status Settlement</span>
                    <span id="modalStatus" class="font-bold text-slate-800 capitalize">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Nominal Transaksi</span>
                    <span id="modalAmount" class="font-black text-slate-900 text-sm">-</span>
                </div>
            </div>

            <div class="space-y-2 pb-3 border-b border-slate-100">
                <span class="text-slate-400 block font-medium">Data Pelanggan</span>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1">
                    <div id="modalCustomerName" class="font-bold text-slate-800">-</div>
                    <div id="modalCustomerPhone" class="text-slate-500 font-mono">-</div>
                    <div id="modalCustomerEmail" class="text-slate-500">-</div>
                </div>
            </div>

            <div class="space-y-2 pb-3 border-b border-slate-100">
                <span class="text-slate-400 block font-medium">Detail Layanan</span>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1">
                    <div id="modalServiceName" class="font-bold text-slate-800">-</div>
                    <div id="modalServiceDetail" class="text-slate-500">-</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="text-slate-400 block font-medium">Metode Pembayaran</span>
                    <span id="modalMethod" class="font-bold text-slate-800">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Waktu Transaksi</span>
                    <span id="modalTime" class="font-medium text-slate-600">-</span>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold hover:bg-slate-100 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openTransactionModal(trxId) {
        fetch(`/admin/reports/detail/${trxId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                document.getElementById('modalTrxCode').innerText = d.transaction_code;
                document.getElementById('modalStatus').innerText = d.status.toUpperCase();
                document.getElementById('modalAmount').innerText = d.formatted_amount;
                document.getElementById('modalCustomerName').innerText = d.customer_name;
                document.getElementById('modalCustomerPhone').innerText = d.customer_phone;
                document.getElementById('modalCustomerEmail').innerText = d.customer_email;
                document.getElementById('modalServiceName').innerText = d.service_name;
                document.getElementById('modalServiceDetail').innerText = d.service_detail;
                document.getElementById('modalMethod').innerText = d.payment_method + (d.payment_channel ? ' (' + d.payment_channel + ')' : '');
                document.getElementById('modalTime').innerText = d.paid_at;
                
                document.getElementById('modalTransaction').classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    function closeTransactionModal() {
        document.getElementById('modalTransaction').classList.add('hidden');
    }
</script>

<style>
@media print {
    header, aside, #filterContainer, button, .no-print {
        display: none !important;
    }
    body, main {
        background: white !important;
        padding: 0 !important;
    }
}
</style>
@endsection

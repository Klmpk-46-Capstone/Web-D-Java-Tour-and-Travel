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
                    <option>Kuartal Ini (Q1)</option>
                    <option>Tahun Berjalan 2025</option>
                </select>
            </div>

            {{-- Export CSV --}}
            <a 
                href="{{ route('admin.reports.export-csv') }}" 
                class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>

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
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700">
                    +14.8% bln lalu
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight my-2">
                Rp 428.850.000
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 mt-2">
                <div>Target Q1: <span class="font-bold text-teal-800">82% tercapai</span></div>
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
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700">
                    +8.2%
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight my-2">
                1.248 <span class="text-base font-semibold text-slate-500">Pax</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 mt-2">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>96 Pax trip akhir pekan ini</span>
                </div>
                <span class="font-bold text-slate-700">98.4% On-Schedule</span>
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
            <div class="text-base font-extrabold text-slate-900 truncate my-1">
                Karimunjawa Paradise 3D...
            </div>
            <div class="text-xs text-slate-600 mb-2">
                <strong class="text-slate-900 font-bold">342 Pax</strong> (Rp 119.700.000)
            </div>
            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 mt-2">
                <div class="flex items-center gap-1 text-amber-500 font-bold">
                    <span>★</span>
                    <span class="text-slate-800">4.9</span>
                    <span class="text-slate-400 font-normal">(184 Ulasan)</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-teal-50 text-teal-800">
                    BAHARI OPEN TRIP
                </span>
            </div>
        </div>

        {{-- CARD 4: Tinjauan Refund & Isu --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-xs transition relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold text-slate-400 tracking-wider uppercase">TINJAUAN REFUND &amp; ISU</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700">
                    Perlu Review Segera
                </span>
            </div>
            <div class="text-2xl sm:text-[26px] font-black text-rose-600 tracking-tight my-2">
                {{ $pendingRefundsCount }} <span class="text-base font-semibold text-rose-500">Tiket Ditahan</span>
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
                        Proyeksi pertumbuhan semester 1 (Jan - Jun 2025) berdasarkan 3 rute unggulan Jawa
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

            {{-- VISUAL CHART CANVAS / SVG SIMULATION MATCHING MOCKUP --}}
            <div class="relative w-full h-64 bg-gradient-to-b from-teal-50/20 to-white rounded-xl border border-slate-100 p-4 flex flex-col justify-between overflow-hidden">
                {{-- Tooltip / Highlight for Mei 2025 (High Season) --}}
                <div class="absolute top-4 right-1/4 z-10 bg-slate-900/95 text-white p-3 rounded-xl shadow-xl text-xs max-w-xs border border-teal-500/30">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-800 pb-1 mb-1.5">
                        <span class="font-extrabold text-teal-300 text-[11px] tracking-wider">MEI 2025 (HIGH SEASON)</span>
                    </div>
                    <div class="font-extrabold text-sm text-white">Rp 98.400.000 <span class="font-normal text-xs text-slate-300">(310 Pax)</span></div>
                    <div class="text-[11px] text-teal-400 mt-1">Karimunjawa + Bromo Dominasi 74%</div>
                </div>

                {{-- Chart SVG Visual representation --}}
                <div class="flex-1 flex items-end justify-between gap-4 pt-10 px-4">
                    {{-- Jan 2025 --}}
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full max-w-[40px] flex items-end gap-1 h-32">
                            <div class="flex-1 bg-teal-500/80 rounded-t h-[45%]"></div>
                            <div class="flex-1 bg-slate-700/80 rounded-t h-[35%]"></div>
                            <div class="flex-1 bg-cyan-300/90 rounded-t h-[25%]"></div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500">Jan 2025</span>
                    </div>

                    {{-- Feb 2025 --}}
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full max-w-[40px] flex items-end gap-1 h-32">
                            <div class="flex-1 bg-teal-500/80 rounded-t h-[55%]"></div>
                            <div class="flex-1 bg-slate-700/80 rounded-t h-[40%]"></div>
                            <div class="flex-1 bg-cyan-300/90 rounded-t h-[30%]"></div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500">Feb 2025</span>
                    </div>

                    {{-- Mar 2025 --}}
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full max-w-[40px] flex items-end gap-1 h-32">
                            <div class="flex-1 bg-teal-500/80 rounded-t h-[65%]"></div>
                            <div class="flex-1 bg-slate-700/80 rounded-t h-[50%]"></div>
                            <div class="flex-1 bg-cyan-300/90 rounded-t h-[35%]"></div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500">Mar 2025</span>
                    </div>

                    {{-- Apr 2025 --}}
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full max-w-[40px] flex items-end gap-1 h-32">
                            <div class="flex-1 bg-teal-500/80 rounded-t h-[75%]"></div>
                            <div class="flex-1 bg-slate-700/80 rounded-t h-[60%]"></div>
                            <div class="flex-1 bg-cyan-300/90 rounded-t h-[40%]"></div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-500">Apr 2025</span>
                    </div>

                    {{-- Mei 2025 (Peak High Season) --}}
                    <div class="flex-1 flex flex-col items-center gap-2 relative">
                        <div class="absolute -top-6 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-extrabold whitespace-nowrap">
                            PEAK
                        </div>
                        <div class="w-full max-w-[40px] flex items-end gap-1 h-32">
                            <div class="flex-1 bg-teal-600 rounded-t h-[95%] shadow-sm"></div>
                            <div class="flex-1 bg-slate-800 rounded-t h-[80%] shadow-sm"></div>
                            <div class="flex-1 bg-cyan-400 rounded-t h-[60%] shadow-sm"></div>
                        </div>
                        <span class="text-[11px] font-extrabold text-teal-800">Mei 2025</span>
                    </div>

                    {{-- Jun 2025 (Est.) --}}
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full max-w-[40px] flex items-end gap-1 h-32 opacity-80">
                            <div class="flex-1 bg-teal-500/60 rounded-t h-[70%] border-t border-dashed border-teal-700"></div>
                            <div class="flex-1 bg-slate-700/60 rounded-t h-[60%] border-t border-dashed border-slate-900"></div>
                            <div class="flex-1 bg-cyan-300/60 rounded-t h-[45%] border-t border-dashed border-cyan-500"></div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">Jun (Est.)</span>
                    </div>
                </div>
            </div>

            {{-- Footer insight note --}}
            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Konversi libur panjang Hari Raya Waisak &amp; Kenaikan Isa Almasih melonjak <strong>38.6%</strong> pada pekan ke-3 Mei.</span>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="font-bold text-teal-700 hover:text-teal-900 whitespace-nowrap">
                    Lihat Log Rinci &gt;
                </a>
            </div>
        </div>

        {{-- RIGHT: Performa Kategori Wisata + Widget BMKG (4 cols on lg) --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            {{-- Performa Kategori Wisata --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-extrabold text-slate-900">Performa Kategori Wisata</h3>
                    <span class="text-[10px] font-bold text-slate-400">Q1-Q2 2025</span>
                </div>
                <p class="text-[11px] text-slate-500 mb-4">Pangsa pasar volume Pax berdasarkan preferensi destinasi pelanggan.</p>

                {{-- Multi-color segmented progress bar --}}
                <div class="h-3 w-full bg-slate-100 rounded-full flex overflow-hidden mb-4 shadow-inner">
                    <div class="bg-teal-600 h-full" style="width: 45%" title="Wisata Bahari 45%"></div>
                    <div class="bg-slate-700 h-full" style="width: 30%" title="Mountain Trekking 30%"></div>
                    <div class="bg-cyan-300 h-full" style="width: 25%" title="Cultural & Heritage 25%"></div>
                </div>

                {{-- Legend & metrics --}}
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                            <span class="font-medium text-slate-700">Wisata Bahari &amp; Island Hopping</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <strong class="text-slate-900">561 Pax</strong>
                            <span class="text-slate-400 font-bold">45%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                            <span class="font-medium text-slate-700">Mountain Trekking &amp; Sunrise</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <strong class="text-slate-900">374 Pax</strong>
                            <span class="text-slate-400 font-bold">30%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-300"></span>
                            <span class="font-medium text-slate-700">Cultural, Heritage &amp; City Tour</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <strong class="text-slate-900">313 Pax</strong>
                            <span class="text-slate-400 font-bold">25%</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Rata-rata kepuasan pax:</span>
                    <span class="flex items-center gap-1 font-extrabold text-slate-800">
                        <span class="text-amber-500">★</span> 4.88 / 5.0
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
                <div>Menampilkan 5 dari 1.248 transaksi bulan ini</div>
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

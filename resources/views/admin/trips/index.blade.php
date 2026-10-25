@extends('layouts.admin')

@section('title', 'Jadwal & Detail Trip')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    // Warna chip kalender berdasarkan tipe rute paket.
    $routeTone = [
        'bahari'   => 'bg-teal-100 text-teal-800 hover:bg-teal-200',
        'gunung'   => 'bg-amber-100 text-amber-800 hover:bg-amber-200',
        'heritage' => 'bg-sky-100 text-sky-800 hover:bg-sky-200',
    ];
    $routeDot = [
        'bahari'   => 'bg-teal-500',
        'gunung'   => 'bg-amber-500',
        'heritage' => 'bg-sky-500',
    ];
    $statusTone = [
        'available'   => 'bg-teal-50 text-teal-800 ring-teal-600/20',
        'almost_full' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'full'        => 'bg-rose-50 text-rose-800 ring-rose-600/20',
        'completed'   => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'cancelled'   => 'bg-slate-100 text-slate-500 ring-slate-400/20',
    ];
    $q = fn (array $extra = []) => array_filter(array_merge([
        'month' => $month->format('Y-m'),
        'package_id' => $packageId,
        'trip_id' => $selectedTrip?->id,
    ], $extra), fn ($v) => $v !== null && $v !== '');
    $inputCls = 'w-full text-xs sm:text-sm bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/15 transition';
    $labelCls = 'block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5';
    $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">Operasional Trip &amp; Wisata</a>
                <span>/</span>
                <span class="text-teal-700">Jadwal &amp; Detail Trip</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Jadwal &amp; Operasional Trip</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-teal-700 ring-1 ring-teal-600/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500 animate-pulse"></span> Live Sync
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Pantau jadwal keberangkatan, kapasitas armada, dan ketersediaan driver di seluruh koridor wisata. Kuota yang diubah di sini langsung berlaku di form booking customer.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.trips.sync') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                @if ($selectedTrip)<input type="hidden" name="trip_id" value="{{ $selectedTrip->id }}">@endif
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs transition hover:border-teal-600/40 hover:text-teal-800 cursor-pointer">
                    <svg class="h-4 w-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sinkronisasi Otomatis
                </button>
            </form>
            <button type="button" onclick="document.getElementById('modalQuickTrip').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800 cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Tambah Jadwal Trip Baru
            </button>
        </div>
    </div>

    {{-- STATISTIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <div class="flex items-start justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Trip Bulan Ini</span>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-teal-50 text-teal-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['total'] }}</span>
                <span class="pb-1 text-sm font-bold text-slate-500">Trip</span>
                @if (! is_null($stats['growth']))
                    <span class="mb-1 ml-auto rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $stats['growth'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $stats['growth'] >= 0 ? '↗ +' : '↘ ' }}{{ $stats['growth'] }}%
                    </span>
                @endif
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Dibanding {{ $month->subMonth()->translatedFormat('F Y') }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <div class="flex items-start justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Trip Sedang Berjalan</span>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-sky-50 text-sky-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 11l18-8-8 18-2-8-8-2z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['running'] }}</span>
                <span class="pb-1 text-sm font-bold text-slate-500">Trip</span>
                @if ($stats['running'] > 0)
                    <span class="mb-1 ml-auto rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-extrabold text-teal-700">● Hari Ini</span>
                @endif
            </div>
            <p class="mt-1 truncate text-[11px] text-slate-400">{{ $stats['running_names'] ?: 'Tidak ada trip yang sedang berjalan' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <div class="flex items-start justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Kapasitas Terisi</span>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-50 text-amber-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <span class="text-3xl font-black text-slate-900 tracking-tight">{{ $stats['occupancy'] }}%</span>
                <span class="mb-1 ml-auto rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $stats['occupancy'] >= 80 ? 'bg-amber-50 text-amber-700' : 'bg-teal-50 text-teal-700' }}">
                    {{ $stats['occupancy'] >= 80 ? 'Okupansi Tinggi' : 'Okupansi Normal' }}
                </span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full {{ $stats['occupancy'] >= 80 ? 'bg-amber-500' : 'bg-teal-600' }} transition-all" style="width: {{ $stats['occupancy'] }}%"></div>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">{{ $stats['booked'] }} dari {{ $stats['quota'] }} kursi</p>
        </div>

        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <div class="flex items-start justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Pendapatan Trip</span>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-teal-800 tracking-tight">{{ $rp($stats['revenue']) }}</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Pembayaran sukses pada trip bulan ini</p>
        </div>
    </div>

    {{-- KALENDER + DETAIL --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        {{-- KALENDER --}}
        <section class="xl:col-span-7 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs" aria-label="Kalender jadwal trip">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-teal-50 text-teal-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 leading-tight">{{ $month->translatedFormat('F Y') }}</h2>
                        <p class="text-[11px] text-slate-400">Penjadwalan Armada &amp; Ekspedisi</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('admin.trips.index', $q(['month' => $prevMonth, 'trip_id' => null])) }}" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-teal-600/40 hover:text-teal-700" aria-label="Bulan sebelumnya">‹</a>
                    <a href="{{ route('admin.trips.index', $q(['month' => $currentMonth, 'trip_id' => null])) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:border-teal-600/40 hover:text-teal-700">Hari Ini</a>
                    <a href="{{ route('admin.trips.index', $q(['month' => $nextMonth, 'trip_id' => null])) }}" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-teal-600/40 hover:text-teal-700" aria-label="Bulan berikutnya">›</a>
                </div>
            </div>

            {{-- Filter paket --}}
            <div class="mt-4 flex flex-wrap gap-1.5">
                <a href="{{ route('admin.trips.index', $q(['package_id' => null, 'trip_id' => null])) }}"
                   class="rounded-full px-3 py-1 text-[11px] font-bold transition {{ ! $packageId ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua ({{ $packageChips->sum('count') }})
                </a>
                @foreach ($packageChips as $chip)
                    <a href="{{ route('admin.trips.index', $q(['package_id' => $chip['id'], 'trip_id' => null])) }}"
                       class="rounded-full px-3 py-1 text-[11px] font-bold transition {{ (int) $packageId === (int) $chip['id'] ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ \Illuminate\Support\Str::limit($chip['name'], 22) }} ({{ $chip['count'] }})
                    </a>
                @endforeach
            </div>

            {{-- Grid kalender --}}
            <div class="mt-4 overflow-x-auto">
                <div class="min-w-[560px]">
                    <div class="grid grid-cols-7 gap-1.5 text-center">
                        @foreach ($dayNames as $i => $dn)
                            <div class="py-1 text-[11px] font-extrabold {{ $i === 6 ? 'text-rose-500' : 'text-slate-500' }}">{{ $dn }}</div>
                        @endforeach
                    </div>
                    <div class="mt-1 grid grid-cols-7 gap-1.5">
                        @foreach ($calendar as $week)
                            @foreach ($week as $day)
                                @php
                                    $isSelectedDay = $selectedTrip && $day['date']->betweenIncluded($selectedTrip->departure_date, $selectedTrip->return_date ?? $selectedTrip->departure_date);
                                    $shown = $day['trips']->take(2);
                                    $more = $day['trips']->count() - $shown->count();
                                @endphp
                                <div class="min-h-[78px] rounded-xl border p-1.5 transition
                                    {{ $day['in_month'] ? 'bg-slate-50/70 border-slate-100' : 'bg-white border-transparent opacity-50' }}
                                    {{ $isSelectedDay ? 'ring-2 ring-teal-600 bg-teal-50/50 border-teal-200' : '' }}">
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-[11px] font-extrabold {{ $day['is_today'] ? 'grid h-5 w-5 place-items-center rounded-full bg-teal-700 text-white' : ($day['is_sunday'] ? 'text-rose-500' : 'text-slate-700') }}">{{ $day['date']->day }}</span>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach ($shown as $t)
                                            @php $tone = $t->status === 'cancelled' ? 'bg-slate-200 text-slate-500 line-through' : ($routeTone[$t->package->route_type ?? ''] ?? 'bg-teal-100 text-teal-800 hover:bg-teal-200'); @endphp
                                            <a href="{{ route('admin.trips.index', $q(['trip_id' => $t->id, 'month' => $month->format('Y-m')])) }}"
                                               title="{{ $t->batch_name }} · {{ $t->booked_seats }}/{{ $t->quota }} pax"
                                               class="block truncate rounded-md px-1.5 py-0.5 text-[9px] font-bold leading-tight transition {{ $tone }}">
                                                {{ \Illuminate\Support\Str::limit($t->package->name ?? $t->batch_name, 13, '') }}
                                            </a>
                                        @endforeach
                                        @if ($more > 0)
                                            <span class="block px-1 text-[9px] font-bold text-slate-400">+{{ $more }} lagi</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Legenda --}}
            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-[11px] font-semibold text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full {{ $routeDot['bahari'] }}"></span> Wisata Bahari</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full {{ $routeDot['gunung'] }}"></span> Gunung &amp; Sunrise</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full {{ $routeDot['heritage'] }}"></span> Heritage &amp; City Tour</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-slate-300"></span> Dibatalkan</span>
            </div>
        </section>

        {{-- PANEL DETAIL --}}
        <section class="xl:col-span-5 rounded-2xl border border-slate-200/90 bg-white shadow-2xs" aria-label="Detail trip">
            <div class="flex border-b border-slate-100 p-1.5 gap-1.5">
                <button type="button" data-tab="list" class="trip-tab flex-1 rounded-xl px-3 py-2 text-xs font-bold transition cursor-pointer">Trip Terjadwal ({{ $monthTrips->count() }})</button>
                <button type="button" data-tab="detail" class="trip-tab flex-1 rounded-xl px-3 py-2 text-xs font-bold transition cursor-pointer">Detail &amp; Form Edit Trip</button>
            </div>

            {{-- TAB: daftar trip --}}
            <div id="tab-list" class="hidden p-4 space-y-2 max-h-[640px] overflow-y-auto">
                @forelse ($monthTrips as $t)
                    @php $isSel = $selectedTrip && $selectedTrip->id === $t->id; @endphp
                    <a href="{{ route('admin.trips.index', $q(['trip_id' => $t->id, 'month' => $month->format('Y-m'), 'tab' => 'detail'])) }}"
                       class="flex items-center gap-3 rounded-xl border p-3 transition {{ $isSel ? 'border-teal-600/40 bg-teal-50/60' : 'border-slate-100 hover:border-teal-600/30 hover:bg-slate-50' }}">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-center leading-none">
                            <div>
                                <div class="text-sm font-black text-slate-800">{{ $t->departure_date->format('d') }}</div>
                                <div class="text-[9px] font-bold uppercase text-slate-400">{{ $t->departure_date->translatedFormat('M') }}</div>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-extrabold text-slate-900">{{ $t->package->name ?? 'Trip Khusus' }}</p>
                            <p class="truncate text-[11px] text-slate-400">{{ $t->batch_name }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-extrabold text-slate-800">{{ $t->booked_seats }}/{{ $t->quota }}</p>
                            <span class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-[9px] font-extrabold ring-1 {{ $statusTone[$t->status] ?? $statusTone['available'] }}">{{ $t->status_label }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-xs text-slate-400">
                        Belum ada trip pada bulan ini. Klik <strong>Tambah Jadwal Trip Baru</strong> untuk membuka kuota.
                    </div>
                @endforelse
            </div>

            {{-- TAB: detail & form --}}
            <div id="tab-detail" class="hidden p-5">
                @if ($selectedTrip)
                    @php
                        $t = $selectedTrip;
                        $tripCode = 'TRIP-' . $t->departure_date->format('Ymd') . '-' . str_pad($t->id, 2, '0', STR_PAD_LEFT);
                    @endphp

                    <div class="flex items-start gap-3 rounded-2xl border border-teal-600/15 bg-teal-50/60 p-4">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-teal-700 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 22V4a1 1 0 011-1h14l-3 5 3 5H5"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-extrabold tracking-wider text-teal-700">{{ $tripCode }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold ring-1 {{ $statusTone[$t->status] ?? $statusTone['available'] }}">{{ $t->status_label }}</span>
                            </div>
                            <h3 class="mt-1 text-sm font-extrabold leading-snug text-slate-900">{{ $t->package->name ?? 'Trip Khusus' }}</h3>
                            <p class="text-[11px] text-slate-500">{{ $t->batch_name }}</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.trips.update', $t) }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="f_package" class="{{ $labelCls }}">Rute Ekspedisi (Paket)</label>
                            <select id="f_package" name="package_id" required class="{{ $inputCls }}">
                                @foreach ($packages as $p)
                                    <option value="{{ $p->id }}" @selected((int) old('package_id', $t->package_id) === $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="f_batch" class="{{ $labelCls }}">Nama Batch</label>
                            <input id="f_batch" type="text" name="batch_name" required maxlength="150" value="{{ old('batch_name', $t->batch_name) }}" class="{{ $inputCls }}">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="f_dep" class="{{ $labelCls }}">Tanggal Berangkat</label>
                                <input id="f_dep" type="date" name="departure_date" required value="{{ old('departure_date', $t->departure_date->format('Y-m-d')) }}" class="{{ $inputCls }}">
                            </div>
                            <div>
                                <label for="f_ret" class="{{ $labelCls }}">Tanggal Kembali</label>
                                <input id="f_ret" type="date" name="return_date" value="{{ old('return_date', $t->return_date?->format('Y-m-d')) }}" class="{{ $inputCls }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="f_time" class="{{ $labelCls }}">Jam Berangkat</label>
                                <input id="f_time" type="time" name="departure_time" value="{{ old('departure_time', $t->departure_time) }}" class="{{ $inputCls }}">
                            </div>
                            <div>
                                <label for="f_quota" class="{{ $labelCls }}">Kuota Kursi (Pax)</label>
                                <input id="f_quota" type="number" name="quota" required min="1" max="500" value="{{ old('quota', $t->quota) }}" class="{{ $inputCls }}">
                            </div>
                        </div>

                        <div>
                            <label for="f_meeting" class="{{ $labelCls }}">Titik Kumpul</label>
                            <input id="f_meeting" type="text" name="meeting_point" maxlength="200" placeholder="Cth: Terminal 1 Bandara Juanda (Gate 2B)" value="{{ old('meeting_point', $t->meeting_point) }}" class="{{ $inputCls }}">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="f_vehicle" class="{{ $labelCls }}">Armada Terjadwal</label>
                                <select id="f_vehicle" name="vehicle_id" class="{{ $inputCls }}">
                                    <option value="">Belum ditentukan</option>
                                    @foreach ($vehicles as $v)
                                        <option value="{{ $v->id }}" @selected((int) old('vehicle_id', $t->vehicle_id) === $v->id)>{{ $v->name }} ({{ $v->seats }} kursi)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="f_driver" class="{{ $labelCls }}">Driver / Tour Leader</label>
                                <input id="f_driver" type="text" name="driver_name" maxlength="120" placeholder="Nama driver" value="{{ old('driver_name', $t->driver_name) }}" class="{{ $inputCls }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <span class="{{ $labelCls }} !mb-1">Harga Per Pax</span>
                                <span class="text-sm font-black text-slate-800">{{ $rp($selectedPrice) }}</span>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <span class="{{ $labelCls }} !mb-1">Total Nilai Paket</span>
                                <span class="text-sm font-black text-teal-800">{{ $rp($selectedTotal) }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $t->booked_seats }} pax terpesan</span>
                            </div>
                        </div>

                        <div>
                            <label for="f_status" class="{{ $labelCls }}">Status Operasional Trip</label>
                            <select id="f_status" name="status" class="{{ $inputCls }} font-bold">
                                @foreach ($statusLabels as $val => $label)
                                    <option value="{{ $val }}" @selected(old('status', $t->status) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[10px] text-slate-400">Status Tersedia / Hampir Penuh / Penuh dihitung otomatis dari okupansi kursi.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800 cursor-pointer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.manifest.index', ['trip_id' => $t->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-teal-600/40 hover:text-teal-800">
                                Buka Manifes
                            </a>
                        </div>
                    </form>

                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <form action="{{ route('admin.trips.quick-add-batch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="trip_id" value="{{ $t->id }}">
                            <button type="submit" class="text-[11px] font-extrabold text-teal-700 hover:text-teal-900 hover:underline cursor-pointer">+ Duplikat ke Pekan Depan</button>
                        </form>
                        <form action="{{ route('admin.trips.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus jadwal trip ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] font-bold text-slate-400 hover:text-rose-600 transition cursor-pointer">Hapus Trip</button>
                        </form>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-xs text-slate-400">
                        Pilih trip pada kalender untuk melihat detail dan mengedit jadwalnya.
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- LOG ARMADA --}}
    <section class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs" aria-label="Log armada aktif">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-teal-50 text-teal-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 13l2-6a2 2 0 012-1.4h10A2 2 0 0119 7l2 6v5h-2M3 13v5h2m0-5h14M7 18a2 2 0 104 0m4 0a2 2 0 104 0"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Log Armada Aktif Hari Ini &amp; Cadangan Garasi</h2>
                    <p class="text-[11px] text-slate-400">Berdasarkan penugasan armada pada jadwal trip</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <span class="rounded-full bg-teal-50 px-2.5 py-1 text-teal-700 ring-1 ring-teal-600/20">{{ $onDuty->count() }} Unit On Duty</span>
                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-sky-700 ring-1 ring-sky-600/20">{{ $standby->count() }} Standby</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($onDuty as $i => $trip)
                <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-xs font-black text-slate-700 ring-1 ring-slate-200">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-extrabold text-slate-900">{{ $trip->vehicle->name }}</p>
                        <p class="truncate text-[11px] text-slate-400">{{ $trip->package->name ?? $trip->batch_name }} · {{ $trip->driver_name ?: 'Driver belum ditentukan' }}</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-2 py-0.5 text-[9px] font-extrabold text-teal-800">On Duty</span>
                </div>
            @endforeach
            @foreach ($standby as $v)
                <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-white p-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-slate-50 text-xs font-black text-slate-400 ring-1 ring-slate-200">R{{ $loop->iteration }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-extrabold text-slate-900">{{ $v->name }}</p>
                        <p class="truncate text-[11px] text-slate-400">{{ $v->seats }} kursi · siap ditugaskan</p>
                    </div>
                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[9px] font-extrabold text-sky-800">Standby</span>
                </div>
            @endforeach
            @if ($onDuty->isEmpty() && $standby->isEmpty())
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400">Belum ada data armada aktif.</div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const params = new URLSearchParams(window.location.search);
        const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
        const initial = (params.get('tab') === 'list') ? 'list' : 'detail';
        const tabs = document.querySelectorAll('.trip-tab');
        const panels = { list: document.getElementById('tab-list'), detail: document.getElementById('tab-detail') };

        function show(name) {
            Object.entries(panels).forEach(([key, el]) => el.classList.toggle('hidden', key !== name));
            tabs.forEach((btn) => {
                const active = btn.dataset.tab === name;
                btn.classList.toggle('bg-teal-700', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('text-slate-500', !active);
                btn.classList.toggle('hover:bg-slate-100', !active);
            });
        }

        tabs.forEach((btn) => btn.addEventListener('click', () => show(btn.dataset.tab)));
        show(hasErrors ? 'detail' : initial);
    })();
</script>
@endpush

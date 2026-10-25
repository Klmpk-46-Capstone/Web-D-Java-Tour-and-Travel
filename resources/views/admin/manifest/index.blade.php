@extends('layouts.admin')

@section('title', 'Manifes Penumpang')

@section('content')
@php
    $inputCls = 'w-full text-xs sm:text-sm bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-600/15 transition';
    $labelCls = 'block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5';
    $payTone = [
        'paid'    => ['Lunas', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        'partial' => ['Bayar DP', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'pending' => ['Menunggu Bayar', 'bg-slate-100 text-slate-600 ring-slate-500/20'],
        'unpaid'  => ['Menunggu Bayar', 'bg-slate-100 text-slate-600 ring-slate-500/20'],
    ];
    $filters = [
        'all' => 'Semua',
        'checked_in' => 'Sudah Hadir',
        'not_checked_in' => 'Belum Hadir',
        'dp' => 'Bayar DP',
        'unpaid' => 'Belum Bayar',
    ];
    $tripCode = $selectedTrip ? 'TRIP-' . $selectedTrip->departure_date->format('Ymd') . '-' . str_pad($selectedTrip->id, 2, '0', STR_PAD_LEFT) : null;
    $queryBase = array_filter(['trip_id' => $selectedTrip?->id, 'q' => $search ?: null]);
@endphp

<style>
    @media print {
        header, aside, .no-print { display: none !important; }
        main { padding: 0 !important; overflow: visible !important; }
        body { background: #fff !important; }
    }
</style>

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                @if (auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.trips.index') }}" class="hover:text-teal-700">Jadwal Operasional</a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">Operasional</a>
                @endif
                <span>/</span>
                @if ($tripCode)<span>{{ $tripCode }}</span><span>/</span>@endif
                <span class="text-teal-700">Manifes Penumpang</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manifes Penumpang &amp; Verifikasi Check-in</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">Verifikasi kehadiran penumpang, status pembayaran, dan titik penjemputan sebelum armada berangkat.</p>
        </div>

        <div class="no-print flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.manifest.index') }}" method="GET" class="flex items-center">
                <label for="trip_select" class="sr-only">Pilih trip</label>
                <select id="trip_select" name="trip_id" onchange="this.form.submit()" class="max-w-[320px] rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-700 shadow-2xs focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/15">
                    @foreach ($trips as $tr)
                        <option value="{{ $tr->id }}" @selected($selectedTrip && $selectedTrip->id === $tr->id)>
                            {{ $tr->departure_date->format('d M') }} · {{ \Illuminate\Support\Str::limit($tr->batch_name, 34) }} ({{ $tr->booked_seats }}/{{ $tr->quota }})
                        </option>
                    @endforeach
                </select>
            </form>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800 cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Lembar Sopir
            </button>
        </div>
    </div>

    @if (! $selectedTrip)
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-400">
            Belum ada jadwal trip aktif. {{ auth()->user()->isSuperAdmin() ? 'Buat jadwal trip terlebih dahulu pada menu Jadwal & Detail Trip.' : 'Hubungi Super Admin untuk membuat jadwal trip.' }}
        </div>
    @else
        {{-- RINGKASAN TRIP --}}
        <section class="grid grid-cols-1 xl:grid-cols-12 gap-4">
            <div class="xl:col-span-8 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-teal-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-teal-700 ring-1 ring-teal-600/20">{{ \Illuminate\Support\Str::limit($selectedTrip->package->category ?? 'Paket Wisata', 36) }}</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-600">Keberangkatan: {{ $selectedTrip->departure_date->translatedFormat('d M Y') }}</span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider ring-1 {{ $selectedTrip->status === 'full' ? 'bg-rose-50 text-rose-700 ring-rose-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">{{ $selectedTrip->status_label }}</span>
                </div>
                <h2 class="mt-3 text-xl font-extrabold tracking-tight text-slate-900">{{ $selectedTrip->package->name ?? $selectedTrip->batch_name }}</h2>
                <p class="text-xs text-slate-500">{{ $selectedTrip->batch_name }}</p>

                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <dt class="{{ $labelCls }} !mb-1">Jadwal Operasional</dt>
                        <dd class="font-extrabold text-slate-800">
                            {{ $selectedTrip->departure_date->translatedFormat('d M') }}
                            @if ($selectedTrip->return_date) – {{ $selectedTrip->return_date->translatedFormat('d M Y') }} @else {{ $selectedTrip->departure_date->translatedFormat('Y') }} @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $labelCls }} !mb-1">Armada / Pengemudi</dt>
                        <dd class="font-extrabold text-slate-800">{{ $selectedTrip->vehicle->name ?? 'Armada belum ditentukan' }}</dd>
                        <dd class="text-slate-400">{{ $selectedTrip->driver_name ?: 'Driver belum ditentukan' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelCls }} !mb-1">Titik Kumpul Utama</dt>
                        <dd class="font-extrabold text-slate-800">{{ $selectedTrip->meeting_point ?: 'Belum diinformasikan' }}</dd>
                        <dd class="text-slate-400">{{ $selectedTrip->departure_time ? 'Pukul ' . $selectedTrip->departure_time . ' tepat' : 'Jam belum ditentukan' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="xl:col-span-4 grid grid-cols-2 gap-4">
                <div class="col-span-2 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="{{ $labelCls }} !mb-0">Kapasitas Kursi Terisi</span>
                        <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-extrabold text-teal-700">{{ $summary['occupancy'] }}% Penuh</span>
                    </div>
                    <div class="mt-2 flex items-end gap-1">
                        <span class="text-3xl font-black tracking-tight text-slate-900">{{ $summary['occupied'] }}</span>
                        <span class="pb-1 text-sm font-bold text-slate-400">/ {{ $summary['quota'] }} Kursi</span>
                    </div>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ $summary['occupancy'] }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400">Sisa {{ $summary['remaining'] }} kursi tersedia</p>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
                    <span class="{{ $labelCls }} !mb-1">Check-in</span>
                    <div class="text-2xl font-black text-teal-700">{{ $summary['checked_in_pax'] }}</div>
                    <p class="text-[11px] text-slate-400">dari {{ $summary['total_pax'] }} pax</p>
                </div>
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
                    <span class="{{ $labelCls }} !mb-1">Belum Hadir</span>
                    <div class="text-2xl font-black text-amber-600">{{ $summary['waiting_pax'] }}</div>
                    <p class="text-[11px] text-slate-400">{{ $summary['waiting_bookings'] }} pemesan</p>
                </div>
            </div>
        </section>

        {{-- TOOLBAR --}}
        <section class="no-print rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <div class="flex flex-col xl:flex-row xl:items-center gap-3">
                <form action="{{ route('admin.manifest.index') }}" method="GET" class="flex flex-1 items-center gap-2">
                    <input type="hidden" name="trip_id" value="{{ $selectedTrip->id }}">
                    @if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <label for="manifest_q" class="sr-only">Cari penumpang</label>
                        <input id="manifest_q" type="text" name="q" value="{{ $search }}" placeholder="Cari nama, kode booking, atau WhatsApp…" class="{{ $inputCls }} pl-9">
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-slate-900 cursor-pointer">Cari</button>
                </form>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" data-open="modalPassenger" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah Penumpang
                    </button>
                    <a href="{{ route('admin.manifest.export', ['trip_id' => $selectedTrip->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-teal-600/40 hover:text-teal-800">
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Ekspor Manifes
                    </a>
                    <button type="button" data-open="modalReminder" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 cursor-pointer">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0012 0a12 12 0 00-10.3 18L0 24l6.2-1.6A12 12 0 1012 0zm-8.5 20a9.9 9.9 0 01-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.9 9.9 0 1112 23.5z"/></svg>
                        Pengingat WA ({{ $reminders->count() }})
                    </button>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($filters as $key => $label)
                    @php
                        $count = match ($key) {
                            'all' => $summary['total_bookings'],
                            'checked_in' => $summary['checked_in_bookings'],
                            'not_checked_in' => $summary['waiting_bookings'],
                            'dp' => $summary['dp_count'],
                            'unpaid' => $summary['unpaid_count'],
                        };
                    @endphp
                    <a href="{{ route('admin.manifest.index', array_merge($queryBase, $key === 'all' ? [] : ['filter' => $key])) }}"
                       class="rounded-full px-3 py-1 text-[11px] font-bold transition {{ $filter === $key ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }} ({{ $count }})
                    </a>
                @endforeach
            </div>
        </section>

        {{-- TABEL MANIFES --}}
        <section class="rounded-2xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Data Pemesan &amp; Dokumen</th>
                            <th class="py-3 px-4">WhatsApp &amp; Kontak</th>
                            <th class="py-3 px-4">Penjemputan &amp; Destinasi</th>
                            <th class="py-3 px-4">Status Bayar</th>
                            <th class="py-3 px-4">Check-in</th>
                            <th class="py-3 px-4 text-center no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($passengers as $i => $b)
                            @php
                                [$payLabel, $payCls] = $payTone[$b->payment_status] ?? ['Menunggu Bayar', 'bg-slate-100 text-slate-600 ring-slate-500/20'];
                                $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($b->customer_name, 0, 2));
                                $wa = preg_replace('/\D+/', '', (string) $b->customer_phone);
                                if (\Illuminate\Support\Str::startsWith($wa, '0')) { $wa = '62' . substr($wa, 1); }
                            @endphp
                            <tr class="transition hover:bg-slate-50/70 {{ $b->booking_status === 'refund_requested' ? 'bg-rose-50/40' : '' }}">
                                <td class="py-3.5 px-4 text-center font-extrabold text-slate-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-teal-50 text-[11px] font-extrabold text-teal-700 ring-1 ring-teal-600/15">{{ $initial }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate font-extrabold text-slate-900">{{ $b->customer_name }}
                                                <span class="ml-1 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-600">{{ $b->pax }} pax</span>
                                            </p>
                                            <p class="font-mono text-[10px] font-bold text-teal-700">#{{ $b->booking_code }}</p>
                                            <p class="text-[10px] {{ $b->identity_file_path ? 'text-emerald-600' : 'text-slate-400' }}">
                                                {{ $b->identity_file_path ? '✓ Dokumen identitas terunggah' : 'Dokumen identitas belum diunggah' }}
                                            </p>
                                            @if ($b->booking_status === 'refund_requested')
                                                <p class="text-[10px] font-extrabold text-rose-600">Pengajuan refund diproses</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-mono font-bold text-slate-800">{{ $b->customer_phone }}</p>
                                    <p class="truncate text-[11px] text-slate-400 max-w-[180px]">{{ $b->customer_email !== '-' ? $b->customer_email : 'Tanpa email' }}</p>
                                </td>
                                <td class="py-3.5 px-4 max-w-[240px]">
                                    <p class="font-extrabold text-slate-800">{{ $b->pickup_location ?: 'Dijemput di titik kumpul' }}</p>
                                    @if ($b->pickup_details)<p class="line-clamp-2 text-[11px] text-slate-400">{{ $b->pickup_details }}</p>@endif
                                    @if ($b->notes)<p class="line-clamp-1 text-[11px] italic text-slate-400">“{{ $b->notes }}”</p>@endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $payCls }}">{{ $payLabel }}</span>
                                    <p class="mt-1 text-[10px] text-slate-400">{{ $b->formatted_total_price }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($b->is_checked_in)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-teal-700 px-2.5 py-1 text-[10px] font-extrabold text-white">✓ Hadir</span>
                                        <p class="mt-1 text-[10px] text-slate-400">{{ $b->checked_in_at->format('d M, H:i') }} WIB</p>
                                    @else
                                        <span class="inline-block rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-amber-700 ring-1 ring-amber-600/20">Belum Hadir</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 no-print">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('admin.manifest.check-in', $b) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="{{ $b->is_checked_in ? 'out' : 'in' }}">
                                            @if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                                            @if ($search)<input type="hidden" name="q" value="{{ $search }}">@endif
                                            @if ($b->is_checked_in)
                                                <button type="submit" title="Batalkan check-in" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-[11px] font-bold text-slate-500 transition hover:border-rose-300 hover:text-rose-600 cursor-pointer">Batal</button>
                                            @elseif ($b->can_check_in)
                                                <button type="submit" title="Check-in penumpang" class="rounded-lg bg-teal-700 px-3 py-1.5 text-[11px] font-bold text-white transition hover:bg-teal-800 cursor-pointer">Check-in</button>
                                            @else
                                                <button type="button" disabled title="Penumpang belum membayar / booking belum terkonfirmasi" class="rounded-lg bg-slate-100 px-3 py-1.5 text-[11px] font-bold text-slate-400 cursor-not-allowed">Check-in</button>
                                            @endif
                                        </form>
                                        @if ($wa)
                                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" title="Chat WhatsApp" class="grid h-8 w-8 place-items-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0012 0a12 12 0 00-10.3 18L0 24l6.2-1.6A12 12 0 1012 0zm-8.5 20a9.9 9.9 0 01-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.9 9.9 0 1112 23.5z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-14 text-center text-sm text-slate-400">
                                    @if ($search || $filter !== 'all')
                                        Tidak ada penumpang yang cocok dengan filter. <a href="{{ route('admin.manifest.index', ['trip_id' => $selectedTrip->id]) }}" class="font-bold text-teal-700 hover:underline">Reset filter</a>
                                    @else
                                        Belum ada penumpang pada trip ini. Gunakan tombol <strong>Tambah Penumpang</strong> untuk pendaftaran loket offline.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/60 px-4 py-3 text-[11px] font-semibold text-slate-500">
                <span>Menampilkan {{ $passengers->count() }} dari {{ $summary['total_bookings'] }} pemesan ({{ $summary['total_pax'] }} pax)</span>
                <span>Kapasitas kursi: {{ $summary['occupied'] }} / {{ $summary['quota'] }} · Sisa {{ $summary['remaining'] }}</span>
            </div>
        </section>
    @endif
</div>

@if ($selectedTrip)
    {{-- MODAL: TAMBAH PENUMPANG --}}
    <div id="modalPassenger" class="no-print hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <button type="button" data-close class="absolute right-4 top-4 text-2xl font-bold text-slate-400 hover:text-slate-600" aria-label="Tutup">&times;</button>
            <h3 class="text-lg font-extrabold text-slate-900">Tambah Penumpang Offline</h3>
            <p class="mb-4 mt-1 text-xs text-slate-500">Pendaftaran loket untuk trip <strong>{{ $selectedTrip->batch_name }}</strong>. Sisa kursi: <strong>{{ $summary['remaining'] }}</strong>. Pembayaran tercatat sebagai transaksi tunai.</p>

            <form action="{{ route('admin.manifest.passengers.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="trip_id" value="{{ $selectedTrip->id }}">
                <div>
                    <label for="p_name" class="{{ $labelCls }}">Nama Pemesan</label>
                    <input id="p_name" type="text" name="customer_name" required maxlength="150" class="{{ $inputCls }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="p_phone" class="{{ $labelCls }}">WhatsApp / HP</label>
                        <input id="p_phone" type="text" name="customer_phone" required maxlength="50" placeholder="08xxxxxxxxxx" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label for="p_pax" class="{{ $labelCls }}">Jumlah Pax</label>
                        <input id="p_pax" type="number" name="pax" required min="1" max="{{ max(1, $summary['remaining']) }}" value="1" class="{{ $inputCls }}">
                    </div>
                </div>
                <div>
                    <label for="p_email" class="{{ $labelCls }}">Email (opsional)</label>
                    <input id="p_email" type="email" name="customer_email" maxlength="150" class="{{ $inputCls }}">
                </div>
                <div>
                    <label for="p_pickup" class="{{ $labelCls }}">Lokasi Penjemputan</label>
                    <input id="p_pickup" type="text" name="pickup_location" maxlength="200" placeholder="Cth: Hotel JW Marriott Surabaya" class="{{ $inputCls }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="p_pay" class="{{ $labelCls }}">Pembayaran Diterima</label>
                        <select id="p_pay" name="payment_status" class="{{ $inputCls }}">
                            <option value="paid">Lunas (100%)</option>
                            <option value="partial">DP 30%</option>
                        </select>
                    </div>
                    <div>
                        <label for="p_notes" class="{{ $labelCls }}">Catatan</label>
                        <input id="p_notes" type="text" name="notes" maxlength="500" class="{{ $inputCls }}">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-close class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 transition hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="rounded-xl bg-teal-700 px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800 cursor-pointer">Simpan Penumpang</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: PENGINGAT WA --}}
    <div id="modalReminder" class="no-print hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <button type="button" data-close class="absolute right-4 top-4 text-2xl font-bold text-slate-400 hover:text-slate-600" aria-label="Tutup">&times;</button>
            <h3 class="text-lg font-extrabold text-slate-900">Pengingat WhatsApp</h3>
            <p class="mb-4 mt-1 text-xs text-slate-500">Penumpang yang sudah bayar tetapi belum check-in. Klik “Kirim” untuk membuka WhatsApp dengan pesan pengingat otomatis.</p>

            <div class="max-h-80 space-y-2 overflow-y-auto">
                @forelse ($reminders as $r)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-extrabold text-slate-900">{{ $r['name'] }}</p>
                            <p class="font-mono text-[10px] text-slate-400">#{{ $r['code'] }}</p>
                        </div>
                        <a href="{{ $r['url'] }}" target="_blank" rel="noopener" class="shrink-0 rounded-lg bg-emerald-600 px-3 py-1.5 text-[11px] font-bold text-white transition hover:bg-emerald-700">Kirim</a>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400">Semua penumpang yang sudah membayar telah check-in. 🎉</div>
                @endforelse
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-open]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const el = document.getElementById(btn.dataset.open);
                el.classList.remove('hidden');
                el.classList.add('flex');
            });
        });
        document.querySelectorAll('[data-close]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const el = btn.closest('.fixed');
                el.classList.add('hidden');
                el.classList.remove('flex');
            });
        });
        @if ($errors->any() && $selectedTrip)
            const modal = document.getElementById('modalPassenger');
            if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        @endif
    })();
</script>
@endpush

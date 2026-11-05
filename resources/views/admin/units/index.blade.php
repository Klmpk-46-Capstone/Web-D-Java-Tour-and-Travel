@extends('layouts.admin')

@section('title', 'Unit & Plat Armada')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">KATALOG PRODUK</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Unit &amp; Plat Armada
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola unit fisik dan plat nomor untuk setiap tipe armada. Ketersediaan dihitung otomatis dari trip dan booking aktif.
            </p>
        </div>

        <a
            href="{{ route('admin.units.create') }}"
            class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Tambah Unit / Plat
        </a>
    </div>

    {{-- RINGKASAN PER TIPE --}}
    <section class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Ringkasan Armada Hari Ini</h2>
                <p class="text-[11px] text-slate-400">Status operasional per {{ $today->translatedFormat('d F Y') }}.</p>
            </div>
            <a href="{{ route('admin.armada.index') }}" class="text-[11px] font-extrabold text-teal-700 hover:text-teal-900 hover:underline">
                Kelola Tipe Armada &rarr;
            </a>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @forelse ($summaries as $summary)
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-extrabold text-slate-900">{{ $summary['vehicle']->name }}</p>
                            <p class="text-[10px] text-slate-400">{{ $summary['vehicle']->category_label }}</p>
                        </div>
                        <span class="rounded-full bg-white px-2 py-0.5 text-[9px] font-extrabold text-slate-500 ring-1 ring-slate-200">
                            {{ $summary['total'] }} unit
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-white px-2 py-2 ring-1 ring-slate-100">
                            <p class="text-sm font-black text-teal-700">{{ $summary['available'] }}</p>
                            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Tersedia</p>
                        </div>
                        <div class="rounded-lg bg-white px-2 py-2 ring-1 ring-slate-100">
                            <p class="text-sm font-black text-amber-700">{{ $summary['in_use'] }}</p>
                            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Terpakai</p>
                        </div>
                        <div class="rounded-lg bg-white px-2 py-2 ring-1 ring-slate-100">
                            <p class="text-sm font-black text-rose-700">{{ $summary['maintenance'] }}</p>
                            <p class="text-[9px] font-bold uppercase tracking-wide text-slate-400">Servis</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400">
                    Belum ada tipe armada. Tambahkan tipe terlebih dahulu di menu Armada.
                </div>
            @endforelse
        </div>
    </section>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('admin.units.index') }}" class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="vehicle_id" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Tipe Armada</label>
                <select id="vehicle_id" name="vehicle_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-700 focus:border-teal-600 focus:bg-white focus:outline-none">
                    <option value="">Semua tipe</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected((int) $vehicleId === $vehicle->id)>{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Status Unit</label>
                <select id="status" name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-700 focus:border-teal-600 focus:bg-white focus:outline-none">
                    <option value="">Semua status</option>
                    <option value="available" @selected($status === 'available')>Tersedia</option>
                    <option value="in_use" @selected($status === 'in_use')>Terpakai</option>
                    <option value="maintenance" @selected($status === \App\Models\VehicleUnit::STATUS_MAINTENANCE)>Servis</option>
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-teal-800">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.units.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:border-teal-600/40 hover:text-teal-800">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- DAFTAR UNIT --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4">PLAT NOMOR</th>
                        <th class="py-3.5 px-4">TIPE ARMADA</th>
                        <th class="py-3.5 px-4">STATUS HARI INI</th>
                        <th class="py-3.5 px-4">CATATAN</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($units as $unit)
                        @php
                            $usage = $usageByUnit[$unit->id] ?? null;
                            $isMaintenance = $unit->status === \App\Models\VehicleUnit::STATUS_MAINTENANCE;
                            $statusText = $isMaintenance ? 'Servis' : ($usage ? 'Terpakai' : 'Tersedia');
                            $statusClass = $isMaintenance
                                ? 'bg-rose-50 text-rose-700 ring-rose-600/20'
                                : ($usage
                                    ? 'bg-amber-50 text-amber-700 ring-amber-600/20'
                                    : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20');
                            $usageUrl = $usage instanceof \App\Models\Trip
                                ? route('admin.trips.index', ['month' => $usage->departure_date?->format('Y-m'), 'trip_id' => $usage->id])
                                : ($usage instanceof \App\Models\Booking
                                    ? route('admin.bookings.show', $usage)
                                    : null);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 font-mono text-[11px] font-extrabold tracking-wider text-white">
                                    {{ $unit->plate_number }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-slate-800 block">{{ $unit->vehicle?->name ?? 'Tipe tidak ditemukan' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $unit->vehicle?->seats ? $unit->vehicle->seats . ' kursi' : '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-extrabold ring-1 {{ $statusClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $isMaintenance ? 'bg-rose-500' : ($usage ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                    {{ $statusText }}
                                </span>
                                @if ($usage)
                                    <div class="mt-1 text-[10px] text-slate-400">
                                        @if ($usage instanceof \App\Models\Trip)
                                            {{ $usage->package?->name ?? $usage->batch_name }}
                                        @else
                                            Booking {{ $usage->booking_code }}
                                        @endif
                                        @if ($usageUrl)
                                            &middot; <a href="{{ $usageUrl }}" class="font-bold text-teal-700 hover:underline">Lihat</a>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-500">{{ \Illuminate\Support\Str::limit($unit->notes ?: '-', 60) }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.units.edit', $unit) }}" class="p-1.5 text-slate-400 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition" title="Edit Unit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.units.toggle-status', $unit) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg transition cursor-pointer {{ $isMaintenance ? 'text-emerald-600 hover:bg-emerald-50' : 'text-amber-600 hover:bg-amber-50' }}" title="{{ $isMaintenance ? 'Aktifkan Unit' : 'Tandai Servis' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" onsubmit="return confirm('Hapus unit ini? Unit yang pernah dipakai tidak dapat dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition cursor-pointer" title="Hapus Unit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                Belum ada unit yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($units->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

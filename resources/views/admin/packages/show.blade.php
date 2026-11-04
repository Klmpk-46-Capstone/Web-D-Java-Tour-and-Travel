@extends('layouts.admin')

@section('title', 'Detail Paket ' . $package->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.packages.index') }}" class="hover:text-teal-700">PAKET WISATA</a>
                <span>/</span>
                <span class="text-teal-700">DETAIL</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                {{ $package->name }}
                @if ($package->is_featured)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-teal-100 text-teal-800">★ Featured</span>
                @endif
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $package->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                    {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('packages.show', $package->slug) }}" target="_blank" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition inline-flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview Customer
            </a>
            <a href="{{ route('admin.packages.edit', $package->id) }}" class="px-4 py-2 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Paket &amp; Itinerary
            </a>
        </div>
    </div>

    {{-- PACKAGE OVERVIEW CARD --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="grid grid-cols-1 md:grid-cols-3">
            <div class="relative h-60 md:h-auto bg-slate-100">
                <img 
                    src="{{ $package->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80' }}" 
                    alt="{{ $package->name }}" 
                    class="w-full h-full object-cover"
                >
                @if ($package->badge)
                    <div class="absolute top-3 left-3 px-2.5 py-1 bg-teal-600 text-white font-extrabold text-[10px] rounded-lg uppercase shadow-sm">
                        {{ $package->badge }}
                    </div>
                @endif
            </div>

            <div class="p-6 md:col-span-2 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase">Kategori</span>
                        <span class="text-xs font-extrabold text-slate-800">{{ $package->category }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase">Rute Wilayah</span>
                        <span class="text-xs font-extrabold text-teal-800 uppercase">{{ $package->route_type }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase">Durasi</span>
                        <span class="text-xs font-extrabold text-slate-800">{{ $package->duration }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase">Harga / Pax</span>
                        <span class="text-sm font-black text-teal-700">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi Paket</h3>
                    <p class="text-xs leading-relaxed text-slate-600">
                        {{ $package->description ?: 'Belum ada deskripsi untuk paket wisata ini.' }}
                    </p>
                </div>

                <div class="flex items-center gap-6 pt-2 text-xs text-slate-500">
                    <span>Rating: <strong class="text-amber-500 font-bold">★ {{ number_format($package->rating, 1) }}</strong> ({{ $package->total_reviews }} ulasan)</span>
                    <span>Total Itinerary: <strong class="text-slate-800 font-bold">{{ $package->itineraries->count() }} Hari Kegiatan</strong></span>
                    <span>Total Batch Terjadwal: <strong class="text-slate-800 font-bold">{{ $package->trips->count() }} Batch</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ITINERARY SECTION --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Rencana Perjalanan (Itinerary)</h2>
                <p class="text-xs text-slate-500">Susunan agenda dan aktivitas per hari yang terintegrasi di sisi admin dan customer.</p>
            </div>
            <a href="{{ route('admin.packages.edit', $package->id) }}" class="text-xs font-bold text-teal-700 hover:underline">
                Kelola Itinerary &rarr;
            </a>
        </div>

        <div class="space-y-3">
            @forelse ($package->itineraries as $it)
                <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/60 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-teal-100 text-teal-800">
                            Hari {{ $it->day_number }}
                        </span>
                        <h3 class="text-xs font-extrabold text-slate-900">
                            {{ $it->title }}
                        </h3>
                    </div>
                    @if ($it->description)
                        <p class="text-xs text-slate-600 leading-relaxed pl-1">
                            {{ $it->description }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center rounded-xl border border-dashed border-slate-300 text-xs text-slate-400">
                    Belum ada susunan itinerary untuk paket ini. Silakan klik <a href="{{ route('admin.packages.edit', $package->id) }}" class="text-teal-700 font-bold underline">Edit Paket</a> untuk menambahkan agenda harian.
                </div>
            @endforelse
        </div>
    </div>

    {{-- JADWAL TRIP TERKAIT --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">Jadwal Keberangkatan (Trip Batches)</h2>
                <p class="text-xs text-slate-500">Batch keberangkatan dan sisa kuota yang dapat dipilih customer saat booking.</p>
            </div>
            <a href="{{ route('admin.trips.index', ['package_id' => $package->id]) }}" class="text-xs font-bold text-teal-700 hover:underline">
                Buka Jadwal &amp; Detail Trip &rarr;
            </a>
        </div>

        @if ($package->trips->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            <th class="py-2.5 px-3">Nama Batch</th>
                            <th class="py-2.5 px-3">Tanggal Berangkat</th>
                            <th class="py-2.5 px-3">Tanggal Kembali</th>
                            <th class="py-2.5 px-3">Armada / Driver</th>
                            <th class="py-2.5 px-3 text-center">Kuota (Terisi / Total)</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($package->trips as $trip)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-800">{{ $trip->batch_name }}</td>
                                <td class="py-2.5 px-3">{{ $trip->departure_date->translatedFormat('d M Y') }}</td>
                                <td class="py-2.5 px-3">{{ $trip->return_date ? $trip->return_date->translatedFormat('d M Y') : '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-500">
                                    {{ $trip->vehicle?->name ?? 'Belum ditentukan' }}
                                    @if($trip->driver_name) • {{ $trip->driver_name }} @endif
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold">
                                    <span class="text-teal-700">{{ $trip->booked_seats }}</span> / {{ $trip->quota }} pax
                                    <span class="block text-[10px] text-slate-400 font-normal">Sisa: {{ $trip->remaining_seats }} kursi</span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $trip->status === 'available' ? 'bg-emerald-50 text-emerald-700' : ($trip->status === 'almost_full' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ $trip->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center rounded-xl border border-dashed border-slate-300 text-xs text-slate-400">
                Belum ada jadwal trip yang dibuka untuk paket ini. Buka jadwal trip baru agar customer dapat memilih tanggal keberangkatan.
            </div>
        @endif
    </div>

</div>
@endsection


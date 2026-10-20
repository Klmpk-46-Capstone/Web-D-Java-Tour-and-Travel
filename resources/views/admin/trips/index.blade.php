@extends('layouts.admin')

@section('title', 'Jadwal & Kuota Trip')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">MANAJEMEN KAPASITAS</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Jadwal &amp; Kuota Trip
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Atur tanggal keberangkatan, alokasi kapasitas kursi, dan batch trip. Jika kuota = 0, form booking customer otomatis terkunci (disabled).
            </p>
        </div>

        <button 
            type="button" 
            onclick="document.getElementById('modalQuickTrip').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            + Buat Batch Trip Baru
        </button>
    </div>

    {{-- TRIPS LIST CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($trips as $t)
            @php
                $percentage = $t->percentage_booked;
                $isFull = $t->is_full;
                $isAlmost = $percentage >= 80 && !$isFull;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between hover:shadow-xs transition">
                <div>
                    {{-- Status Badge & Batch Name --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="text-xs font-bold text-teal-700 uppercase tracking-wide">
                            {{ $t->package->name ?? 'Trip Khusus' }}
                        </span>
                        @if ($isFull)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800">
                                Penuh (0 Kursi)
                            </span>
                        @elseif ($isAlmost)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                {{ $percentage }}% Penuh
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-100 text-teal-800">
                                {{ $t->remaining_seats }} Kursi Tersedia
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 mb-3 leading-snug">
                        {{ $t->batch_name }}
                    </h3>

                    <div class="space-y-1.5 text-xs text-slate-600 mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Keberangkatan:</span>
                            <strong class="text-slate-800">{{ $t->departure_date->translatedFormat('l, d F Y') }}</strong>
                        </div>
                        @if ($t->return_date)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Kepulangan:</span>
                                <span class="text-slate-700">{{ $t->return_date->translatedFormat('d F Y') }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Terisi / Kapasitas:</span>
                            <span class="font-extrabold text-slate-900">{{ $t->booked_seats }} / {{ $t->quota }} Pax</span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 mb-1">
                            <span>Okupansi Kursi</span>
                            <span class="{{ $isFull ? 'text-rose-600 font-bold' : ($isAlmost ? 'text-amber-600 font-bold' : 'text-teal-700 font-bold') }}">
                                {{ $percentage }}%
                            </span>
                        </div>
                        <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div 
                                class="h-full rounded-full transition-all duration-500 {{ $isFull ? 'bg-rose-500' : ($isAlmost ? 'bg-amber-500' : 'bg-teal-600') }}" 
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <form action="{{ route('admin.trips.quick-add-batch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="trip_id" value="{{ $t->id }}">
                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-teal-700 hover:text-teal-900 cursor-pointer">
                            <span>+ Buka Batch Baru</span>
                        </button>
                    </form>

                    <div class="flex items-center gap-2">
                        <a 
                            href="{{ route('admin.manifest.index', ['trip_id' => $t->id]) }}" 
                            class="px-2.5 py-1 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
                        >
                            Manifes
                        </a>
                        <form action="{{ route('admin.trips.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal trip batch ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-400">
                Belum ada jadwal trip yang dibuat. Klik tombol "+ Buat Batch Trip Baru" untuk membuka kuota.
            </div>
        @endforelse
    </div>

    @if ($trips->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
            {{ $trips->links() }}
        </div>
    @endif

</div>
@endsection

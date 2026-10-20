@extends('layouts.admin')

@section('title', 'Manifes Penumpang Trip')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">LOGISTIK &amp; ARMADA</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Manifes Penumpang
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Roster resmi manifes penumpang per jadwal batch trip untuk koordinasi tour leader, driver armada, dan agen penyeberangan pelabuhan.
            </p>
        </div>

        <button 
            type="button" 
            onclick="window.print()"
            class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Manifes Resmi
        </button>
    </div>

    {{-- TRIP SELECTOR & BATCH DETAILS --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
        <form action="{{ route('admin.manifest.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Pilih Batch Trip:</label>
                <select name="trip_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600">
                    @foreach ($trips as $tr)
                        <option value="{{ $tr->id }}" {{ $selectedTrip && $selectedTrip->id == $tr->id ? 'selected' : '' }}>
                            {{ $tr->batch_name }} ({{ $tr->departure_date->format('d/m/Y') }}) - {{ $tr->booked_seats }}/{{ $tr->quota }} Pax
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($selectedTrip)
                <div class="flex items-center gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-bold text-[10px] uppercase">TANGGAL KEBERANGKATAN</span>
                        <span class="font-extrabold text-slate-900">{{ $selectedTrip->departure_date->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-bold text-[10px] uppercase">TOTAL PAX TERKONFIRMASI</span>
                        <span class="font-extrabold text-teal-800">{{ $selectedTrip->booked_seats }} / {{ $selectedTrip->quota }} Kursi</span>
                    </div>
                </div>
            @endif
        </form>
    </div>

    {{-- PASSENGERS MANIFEST TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                Daftar Penumpang Roster ({{ $passengers->count() }} Pemesan)
            </h3>
            <span class="text-xs font-bold text-slate-500">
                Total Akumulasi Pax: {{ $passengers->sum('pax') }} Orang
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4 w-12 text-center">NO</th>
                        <th class="py-3 px-4">BOOKING ID</th>
                        <th class="py-3 px-4">NAMA PENUMPANG</th>
                        <th class="py-3 px-4">KONTAK WHATSAPP / HP</th>
                        <th class="py-3 px-4 text-center">JUMLAH PAX</th>
                        <th class="py-3 px-4">METODE BAYAR</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4">CATATAN KHUSUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($passengers as $idx => $p)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-mono font-extrabold text-teal-700">#{{ $p->booking_code }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $p->customer_name }}</td>
                            <td class="py-3 px-4 text-slate-600 font-mono">{{ $p->customer_phone }}</td>
                            <td class="py-3 px-4 text-center font-black text-slate-800">{{ $p->pax }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $p->payment_method }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700">
                                    LUNAS
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 italic">
                                {{ $p->notes ?? 'Tidak ada preferensi khusus' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada manifes penumpang terkonfirmasi untuk batch trip ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

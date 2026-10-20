@extends('layouts.admin')

@section('title', 'Daftar Reservasi Booking')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">RESERVASI MASUK</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Daftar Booking &amp; Reservasi
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola status pemesanan customer, konfirmasi pembayaran, dan detail reservasi aktif.
            </p>
        </div>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari Booking ID, nama customer, email, telp..." 
                class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600 w-full sm:w-72"
            >

            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:bg-white focus:outline-none focus:border-teal-600">
                <option value="">Semua Status Booking</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi (Confirmed)</option>
                <option value="refund_requested" {{ request('status') === 'refund_requested' ? 'selected' : '' }}>Pengajuan Refund</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
                Cari
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.bookings.index') }}" class="px-3 py-2 text-xs text-rose-600 font-bold hover:underline">Reset</a>
            @endif
        </form>

        <div class="text-xs font-semibold text-slate-500 shrink-0">
            Total: <strong class="text-slate-900">{{ $bookings->total() }}</strong> Booking Terdaftar
        </div>
    </div>

    {{-- BOOKINGS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">BOOKING ID</th>
                        <th class="py-3 px-4">PELANGGAN</th>
                        <th class="py-3 px-4">PAKET WISATA</th>
                        <th class="py-3 px-4">JADWAL TRIP</th>
                        <th class="py-3 px-4 text-center">PAX</th>
                        <th class="py-3 px-4 text-right">TOTAL BIAYA</th>
                        <th class="py-3 px-4">METODE BAYAR</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($bookings as $b)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono font-black text-teal-700 whitespace-nowrap">
                                #{{ $b->booking_code }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $b->customer_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $b->customer_email }} • {{ $b->customer_phone }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $b->package->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                {{ $b->trip && $b->trip->departure_date ? $b->trip->departure_date->translatedFormat('d M Y') : 'Open Date' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ $b->pax }} Pax
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($b->total_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                {{ $b->payment_method }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($b->booking_status === 'confirmed')
                                    <span class="px-2.5 py-1 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        Terkonfirmasi
                                    </span>
                                @elseif ($b->booking_status === 'refund_requested')
                                    <span class="px-2.5 py-1 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 animate-pulse">
                                        Review Refund
                                    </span>
                                @elseif ($b->booking_status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded text-[10px] font-extrabold bg-rose-100 text-rose-700">
                                        Dibatalkan / Refund
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                        {{ ucfirst($b->booking_status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada data reservasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

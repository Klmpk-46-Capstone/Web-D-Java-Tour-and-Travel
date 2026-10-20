@extends('layouts.admin')

@section('title', 'Manajemen Akun Customer')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">CRM &amp; USER BASE</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Akun Customer
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Basis data akun pelanggan terdaftar, riwayat reservasi, dan kontak WhatsApp.
            </p>
        </div>

        <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 shadow-2xs">
            Total Pelanggan Terdaftar: <span class="text-teal-800 font-extrabold">{{ $totalCustomers }}</span>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex items-center gap-3">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari nama, email, atau nomor WhatsApp pelanggan..." 
                class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600 flex-1 max-w-md"
            >
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="text-xs text-rose-600 font-bold hover:underline">Reset</a>
            @endif
        </form>
    </div>

    {{-- CUSTOMERS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">NAMA PELANGGAN</th>
                        <th class="py-3 px-4">EMAIL</th>
                        <th class="py-3 px-4">WHATSAPP</th>
                        <th class="py-3 px-4 text-center">TOTAL BOOKING</th>
                        <th class="py-3 px-4">TERDAFTAR SEJAK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($customers as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $c->name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-mono">
                                {{ $c->email }}
                            </td>
                            <td class="py-3.5 px-4 text-teal-800 font-bold font-mono">
                                {{ $c->whatsapp ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-teal-800">
                                    {{ $c->bookings_count }} Reservasi
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $c->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Belum ada pelanggan yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

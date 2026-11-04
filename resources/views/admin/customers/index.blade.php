@extends('layouts.admin')

@section('title', 'Manajemen Akun Customer')

@section('content')
<div class="space-y-6">

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-2xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-2xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

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
                Basis data akun pelanggan, monitoring status keaktifan login, riwayat reservasi transparan, dan kontrol keamanan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 shadow-2xs">
                Total Pelanggan: <span class="text-teal-800 font-extrabold">{{ $totalCustomers }}</span>
            </span>
        </div>
    </div>

    {{-- METRIC STATS CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-400 mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider">Total Pelanggan</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $totalCustomers }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Terdaftar di sistem</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-400 mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider">Pelanggan Aktif</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <p class="text-2xl font-black text-emerald-700">{{ $activeCount }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Status akun normal</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-400 mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider">Online Sekarang</span>
                <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></span>
            </div>
            <p class="text-2xl font-black text-teal-700">{{ $onlineCount }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Aktif 15 menit terakhir</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-400 mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider">Blacklist / Diblokir</span>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            </div>
            <p class="text-2xl font-black text-rose-700">{{ $blacklistedCount }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Akses login ditutup</p>
        </div>
    </div>

    {{-- SEARCH & FILTER TABS --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Filter Status Tabs --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <a 
                    href="{{ route('admin.customers.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" 
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $status === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    Semua ({{ $totalCustomers }})
                </a>
                <a 
                    href="{{ route('admin.customers.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}" 
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $status === 'active' ? 'bg-teal-700 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    Aktif ({{ $activeCount }})
                </a>
                <a 
                    href="{{ route('admin.customers.index', array_merge(request()->except('status', 'page'), ['status' => 'online'])) }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $status === 'online' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    <span class="w-2 h-2 rounded-full {{ $status === 'online' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    Online ({{ $onlineCount }})
                </a>
                <a 
                    href="{{ route('admin.customers.index', array_merge(request()->except('status', 'page'), ['status' => 'blacklisted'])) }}" 
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $status === 'blacklisted' ? 'bg-rose-700 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    Blacklist ({{ $blacklistedCount }})
                </a>
            </div>

            {{-- Search Bar --}}
            <form action="{{ route('admin.customers.index') }}" method="GET" class="flex items-center gap-2">
                @if($status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari nama, email, WhatsApp..." 
                        class="bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-1.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600 w-64 transition"
                    >
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.customers.index', ['status' => $status]) }}" class="text-xs text-rose-600 font-bold hover:underline">Reset</a>
                @endif
            </form>
        </div>
    </div>

    {{-- CUSTOMERS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4 text-center w-12">NO</th>
                        <th class="py-3 px-4">NAMA PELANGGAN</th>
                        <th class="py-3 px-4">WHATSAPP &amp; KONTAK</th>
                        <th class="py-3 px-4">STATUS KEAKTIFAN</th>
                        <th class="py-3 px-4">STATUS AKUN</th>
                        <th class="py-3 px-4 text-center">TOTAL RESERVASI</th>
                        <th class="py-3 px-4">TERDAFTAR SEJAK</th>
                        <th class="py-3 px-4 text-center">AKSI KONTROL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($customers as $i => $c)
                        @php
                            $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($c->name, 0, 2));
                            $actBadge = $c->activity_status_badge;
                            $wa = preg_replace('/\D+/', '', (string) ($c->whatsapp ?? ''));
                            if (\Illuminate\Support\Str::startsWith($wa, '0')) { $wa = '62' . substr($wa, 1); }
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition {{ $c->is_blacklisted ? 'bg-rose-50/20' : '' }}">
                            <td class="py-3.5 px-4 text-center font-extrabold text-slate-400">
                                {{ str_pad($customers->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-teal-50 text-[11px] font-extrabold text-teal-800 ring-1 ring-teal-600/15">
                                        {{ $initial }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('admin.customers.show', $c) }}" class="font-extrabold text-slate-900 hover:text-teal-700 transition truncate">
                                                {{ $c->name }}
                                            </a>
                                            @if($c->google_id)
                                                <span class="text-[9px] font-bold text-sky-600 bg-sky-50 px-1.5 py-0.2 rounded ring-1 ring-sky-500/20" title="Akun login Google">G-Auth</span>
                                            @endif
                                        </div>
                                        <p class="text-slate-500 font-mono text-[11px] truncate">{{ $c->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($c->whatsapp)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-slate-800 text-xs">{{ $c->whatsapp }}</span>
                                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" title="Kirim Pesan WhatsApp" class="p-1 rounded-md text-emerald-600 hover:bg-emerald-50 transition">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0012 0a12 12 0 00-10.3 18L0 24l6.2-1.6A12 12 0 1012 0zm-8.5 20a9.9 9.9 0 01-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.9 9.9 0 1112 23.5z"/></svg>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Tidak ada nomor</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $actBadge['dot_class'] }}"></span>
                                    <span class="font-medium text-slate-700 text-[11px]">{{ $actBadge['label'] }}</span>
                                </div>
                                @if($c->last_login_at)
                                    <p class="text-[10px] text-slate-400 mt-0.5">Login: {{ $c->last_login_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($c->is_blacklisted)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20" title="{{ $c->blacklist_reason }}">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Blacklisted
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                        ✓ Aktif Normal
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-teal-800 ring-1 ring-teal-600/15">
                                    {{ $c->bookings_count }} Reservasi
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $c->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a 
                                    href="{{ route('admin.customers.show', $c) }}" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:border-teal-500 hover:text-teal-700 hover:bg-slate-50 shadow-2xs transition"
                                    title="Buka detail profil, riwayat reservasi & kontrol keamanan customer"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                                    <p class="font-bold text-slate-600">Tidak ada data customer</p>
                                    <p class="text-xs text-slate-400 mt-1">Tidak ada akun pelanggan yang sesuai dengan filter atau kata kunci pencarian.</p>
                                </div>
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

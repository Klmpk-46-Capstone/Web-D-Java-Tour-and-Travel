@extends('layouts.admin')

@section('title', 'Manajemen Paket & Itinerary')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">KATALOG PRODUK</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Paket Wisata &amp; Itinerary
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola paket tur Jawa Timur, susunan jadwal harian (itinerary), dan status aktivasi katalog secara live.
            </p>
        </div>

        <a 
            href="{{ route('admin.packages.create') }}" 
            class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Paket Baru
        </a>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form action="{{ route('admin.packages.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari nama paket wisata..." 
                class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600 w-full sm:w-64"
            >

            <select name="route_type" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:bg-white focus:outline-none focus:border-teal-600">
                <option value="">Semua Rute</option>
                <option value="bahari" {{ request('route_type') === 'bahari' ? 'selected' : '' }}>Bahari (Karimunjawa)</option>
                <option value="gunung" {{ request('route_type') === 'gunung' ? 'selected' : '' }}>Gunung (Bromo / Dieng / Ijen)</option>
                <option value="heritage" {{ request('route_type') === 'heritage' ? 'selected' : '' }}>Heritage (Jogja / Borobudur)</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Filter
            </button>
            @if(request('search') || request('route_type'))
                <a href="{{ route('admin.packages.index') }}" class="px-3 py-2 text-xs text-rose-600 font-bold hover:underline">Reset</a>
            @endif
        </form>

        <div class="text-xs font-semibold text-slate-500 shrink-0">
            Total: <strong class="text-slate-900">{{ $packages->total() }}</strong> Paket Terdaftar
        </div>
    </div>

    {{-- TABLE LIST --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4">PAKET WISATA</th>
                        <th class="py-3.5 px-4">KATEGORI &amp; RUTE</th>
                        <th class="py-3.5 px-4">DURASI</th>
                        <th class="py-3.5 px-4">HARGA</th>
                        <th class="py-3.5 px-4 text-center">REKOMENDASI (FEATURED)</th>
                        <th class="py-3.5 px-4 text-center">STATUS</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($packages as $pkg)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img 
                                        src="{{ $pkg->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=120&q=80' }}" 
                                        alt="{{ $pkg->name }}" 
                                        class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0"
                                    >
                                    <div>
                                        <div class="font-extrabold text-slate-900 text-sm hover:text-teal-700">
                                            {{ $pkg->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                            <span class="text-amber-500 font-bold">★ {{ $pkg->rating }}</span>
                                            <span>•</span>
                                            <span>{{ $pkg->total_reviews }} Ulasan</span>
                                            <span>•</span>
                                            <span class="text-teal-700 font-bold">{{ $pkg->itineraries->count() }} Hari Itinerary</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-700 block">{{ $pkg->category }}</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $pkg->route_type === 'bahari' ? 'bg-teal-50 text-teal-800' : ($pkg->route_type === 'gunung' ? 'bg-slate-100 text-slate-800' : 'bg-cyan-50 text-cyan-800') }}">
                                    {{ $pkg->route_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-700">
                                {{ $pkg->duration }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                Rp {{ number_format($pkg->price, 0, ',', '.') }}
                            </td>
                            {{-- Point 2: Featured Toggle --}}
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.packages.toggle-featured', $pkg->id) }}" method="POST">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-extrabold transition cursor-pointer {{ $pkg->is_featured ? 'bg-teal-100 text-teal-800 ring-1 ring-teal-600/30' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}"
                                        title="Klik untuk toggle status Rekomendasi Utama pada customer dashboard"
                                    >
                                        <span>★</span>
                                        <span>{{ $pkg->is_featured ? 'Featured' : 'Biasa' }}</span>
                                    </button>
                                </form>
                            </td>
                            {{-- Active Status Toggle --}}
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.packages.toggle-status', $pkg->id) }}" method="POST">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold transition cursor-pointer {{ $pkg->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full {{ $pkg->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span>{{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            </td>
                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a 
                                        href="{{ route('packages.show', $pkg->slug) }}" 
                                        target="_blank"
                                        class="p-1.5 text-slate-400 hover:text-teal-700 rounded-lg hover:bg-slate-100 transition" 
                                        title="Preview Halaman Customer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a 
                                        href="{{ route('admin.packages.edit', $pkg->id) }}" 
                                        class="p-1.5 text-slate-400 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition" 
                                        title="Edit Paket & Itinerary"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.packages.destroy', $pkg->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket wisata ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition cursor-pointer" title="Hapus Paket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Tidak ada paket wisata yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($packages->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $packages->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

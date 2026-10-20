@extends('layouts.admin')

@section('title', 'Rekomendasi Utama (Featured Packages)')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">PROMOSI &amp; HIGHLIGHT</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>Rekomendasi Utama</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-teal-100 text-teal-800">
                    Live Customer Showcase
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Paket yang ditandai sebagai 'Featured' di sini akan otomatis tayang di banner utama dan highlight section beranda customer.
            </p>
        </div>

        <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Lihat Beranda Customer
        </a>
    </div>

    {{-- CURRENTLY FEATURED CARDS GRID --}}
    <div>
        <h2 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-3">
            SEDANG AKTIF DI BANNER UTAMA CUSTOMER ({{ $featuredPackages->count() }} Paket)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($featuredPackages as $fp)
                <div class="bg-white rounded-2xl border-2 border-teal-500/40 p-5 shadow-xs flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-3 right-3 z-10">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-teal-700 text-white shadow-sm">
                            ★ REKOMENDASI UTAMA
                        </span>
                    </div>

                    <div>
                        <div class="h-44 rounded-xl overflow-hidden mb-4 relative bg-slate-100">
                            <img src="{{ $fp->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $fp->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <div class="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                {{ $fp->badge ?? $fp->category }}
                            </div>
                        </div>

                        <div class="text-xs font-bold text-teal-700 uppercase tracking-wide mb-1">{{ $fp->category }}</div>
                        <h3 class="text-base font-extrabold text-slate-900 mb-2 leading-snug">{{ $fp->name }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed">{{ $fp->description }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase text-slate-400 block font-bold">Harga Paket</span>
                            <span class="text-sm font-extrabold text-slate-900">Rp {{ number_format($fp->price, 0, ',', '.') }}</span>
                        </div>

                        <form action="{{ route('admin.packages.toggle-featured', $fp->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition cursor-pointer">
                                Copot Rekomendasi
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center text-slate-400">
                    Belum ada paket yang di-featured. Pilih dari daftar di bawah untuk memunculkan ke dashboard customer.
                </div>
            @endforelse
        </div>
    </div>

    {{-- PACKAGES AVAILABLE TO BE FEATURED --}}
    @if ($availablePackages->isNotEmpty())
        <div class="pt-4">
            <h2 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-3">
                PAKET LAINNYA DAPAT DITAMBAHKAN KE REKOMENDASI ({{ $availablePackages->count() }} Paket)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($availablePackages as $ap)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $ap->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=120&q=80' }}" class="w-12 h-12 rounded-xl object-cover shrink-0">
                            <div class="min-w-0">
                                <div class="font-bold text-xs text-slate-900 truncate">{{ $ap->name }}</div>
                                <div class="text-[11px] text-slate-400">Rp {{ number_format($ap->price, 0, ',', '.') }} • {{ $ap->category }}</div>
                            </div>
                        </div>

                        <form action="{{ route('admin.packages.toggle-featured', $ap->id) }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 hover:bg-teal-100 text-xs font-extrabold transition cursor-pointer">
                                + Jadikan Featured
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

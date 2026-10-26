@extends('layouts.admin')

@section('title', 'Manajemen Armada')

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
                Manajemen Armada
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola unit rental, tarif sewa, foto armada, dan status tampil di katalog customer.
            </p>
        </div>

        <a
            href="{{ route('admin.armada.create') }}"
            class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Armada Baru
        </a>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <form action="{{ route('admin.armada.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari nama armada, badge, atau transmisi..."
                class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600 w-full sm:w-72"
            >

            <select name="category" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:bg-white focus:outline-none focus:border-teal-600">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="transmission" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:bg-white focus:outline-none focus:border-teal-600">
                <option value="">Semua Transmisi</option>
                <option value="at" {{ $transmission === 'at' ? 'selected' : '' }}>Automatic / Matic</option>
                <option value="mt" {{ $transmission === 'mt' ? 'selected' : '' }}>Manual</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Filter
            </button>

            @if ($search !== '' || $category || $transmission)
                <a href="{{ route('admin.armada.index') }}" class="px-3 py-2 text-xs text-rose-600 font-bold hover:underline">Reset</a>
            @endif
        </form>

        <div class="text-xs font-semibold text-slate-500 shrink-0">
            Total: <strong class="text-slate-900">{{ $vehicles->total() }}</strong> Armada Terdaftar
        </div>
    </div>

    {{-- TABLE LIST --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4">ARMADA</th>
                        <th class="py-3.5 px-4">KATEGORI &amp; TRANSMISI</th>
                        <th class="py-3.5 px-4">KAPASITAS</th>
                        <th class="py-3.5 px-4">TARIF SEWA</th>
                        <th class="py-3.5 px-4 text-center">STATUS</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if ($vehicle->image_url)
                                        <img
                                            src="{{ $vehicle->image_url }}"
                                            alt="{{ $vehicle->name }}"
                                            class="w-14 h-12 rounded-xl object-cover border border-slate-200 shrink-0"
                                        >
                                    @else
                                        <div class="w-14 h-12 rounded-xl border border-slate-200 bg-slate-100 grid place-items-center text-slate-400 shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h14M6 17l-1-4 2-5h10l2 5-1 4M6 17v2m12-2v2M7 13h10"/></svg>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-extrabold text-slate-900 text-sm">
                                            {{ $vehicle->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 flex flex-wrap items-center gap-2 mt-0.5">
                                            @if ($vehicle->badge)
                                                <span class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold uppercase tracking-wide">
                                                    {{ $vehicle->badge }}
                                                </span>
                                            @endif
                                            <span class="text-amber-500 font-bold">
                                                &#9733; {{ number_format((float) ($vehicle->reviews_avg_rating ?? 0), 1, ',', '.') }}
                                            </span>
                                            <span>&middot;</span>
                                            <span>{{ $vehicle->reviews_count }} Ulasan</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-700 block">{{ $vehicle->category_label }}</span>
                                <span class="text-[11px] text-slate-500">{{ $vehicle->transmission ?: 'Transmisi belum diisi' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-700 block">{{ $vehicle->seats }} kursi</span>
                                <span class="text-[11px] text-slate-500">{{ $vehicle->luggage_capacity }} koper</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-extrabold text-slate-900 block">
                                    {{ $vehicle->formatted_lowest_price ?? $vehicle->formatted_price }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    @if ($vehicle->price_with_driver)
                                        Driver {{ 'Rp ' . number_format($vehicle->price_with_driver, 0, ',', '.') }}
                                    @endif
                                    @if ($vehicle->price_with_driver && $vehicle->price_without_driver)
                                        &middot;
                                    @endif
                                    @if ($vehicle->price_without_driver)
                                        Lepas kunci {{ 'Rp ' . number_format($vehicle->price_without_driver, 0, ',', '.') }}
                                    @endif
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.armada.toggle-status', $vehicle->id) }}" method="POST">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold transition cursor-pointer {{ $vehicle->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}"
                                        title="Klik untuk mengubah status tampil di katalog customer"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full {{ $vehicle->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span>{{ $vehicle->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a
                                        href="{{ route('armada.show', $vehicle->slug) }}"
                                        target="_blank"
                                        class="p-1.5 text-slate-400 hover:text-teal-700 rounded-lg hover:bg-slate-100 transition"
                                        title="Lihat Detail Armada di Halaman Customer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a
                                        href="{{ route('admin.armada.edit', $vehicle->id) }}"
                                        class="p-1.5 text-slate-400 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition"
                                        title="Edit Armada"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.armada.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus armada ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition cursor-pointer" title="Hapus Armada">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                Tidak ada armada yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vehicles->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

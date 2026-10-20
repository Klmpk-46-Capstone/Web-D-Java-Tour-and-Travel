@extends('layouts.admin')

@section('title', 'Moderasi Ulasan Pelanggan')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">FEEDBACK &amp; REPUTASI</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Moderasi Ulasan Pelanggan
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Review dari customer masuk ke antrean di sini. Aksi sembunyikan atau hapus oleh admin langsung mencabut ulasan dari halaman publik detail paket.
            </p>
        </div>
    </div>

    {{-- SUMMARY BADGES --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Ulasan Masuk</span>
            <span class="text-2xl font-black text-slate-900">{{ $totalReviews }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-emerald-600 block">Tayang Publik</span>
            <span class="text-2xl font-black text-emerald-700">{{ $approvedCount }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-amber-600 block">Disembunyikan (Hidden)</span>
            <span class="text-2xl font-black text-amber-700">{{ $hiddenCount }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-rose-600 block">Menunggu Moderasi</span>
            <span class="text-2xl font-black text-rose-700">{{ $pendingCount }}</span>
        </div>
    </div>

    {{-- REVIEWS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Antrean Ulasan Produk</h3>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reviews.index') }}" class="px-2.5 py-1 text-xs rounded-lg font-bold {{ !request('status') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Semua</a>
                <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}" class="px-2.5 py-1 text-xs rounded-lg font-bold {{ request('status') === 'approved' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Tayang</a>
                <a href="{{ route('admin.reviews.index', ['status' => 'hidden']) }}" class="px-2.5 py-1 text-xs rounded-lg font-bold {{ request('status') === 'hidden' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Disembunyikan ({{ $hiddenCount }})</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">PELANGGAN</th>
                        <th class="py-3 px-4">PAKET WISATA</th>
                        <th class="py-3 px-4">RATING</th>
                        <th class="py-3 px-4">KOMENTAR / ULASAN</th>
                        <th class="py-3 px-4 text-center">STATUS VISIBILITAS</th>
                        <th class="py-3 px-4 text-right">AKSI MODERASI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($reviews as $rv)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $rv->customer_name }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800">
                                {{ $rv->package->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="text-amber-500 font-black">
                                    @for ($i = 0; $i < $rv->rating; $i++) ★ @endfor
                                </span>
                                <span class="text-slate-500 font-bold ml-1">({{ $rv->rating }}/5)</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-sm text-slate-600 leading-relaxed">
                                {{ $rv->comment }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($rv->is_hidden)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                        Disembunyikan (Nonaktif Publik)
                                    </span>
                                @elseif ($rv->is_approved)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        Tayang di Publik
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                        Antrean Baru
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Toggle Visibility Form --}}
                                    <form action="{{ route('admin.reviews.toggle-visibility', $rv->id) }}" method="POST">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer {{ $rv->is_hidden ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}"
                                            title="Sembunyikan atau tampilkan ulasan"
                                        >
                                            {{ $rv->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}
                                        </button>
                                    </form>

                                    {{-- Delete Review --}}
                                    <form action="{{ route('admin.reviews.destroy', $rv->id) }}" method="POST" onsubmit="return confirm('Hapus ulasan ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Hapus Ulasan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada ulasan yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

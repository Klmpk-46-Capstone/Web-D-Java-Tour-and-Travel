@extends('layouts.admin')

@section('title', 'Edit Unit Armada')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <div class="mb-1 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
            <a href="{{ route('admin.units.index') }}" class="hover:text-teal-700">UNIT &amp; PLAT</a>
            <span>/</span>
            <span class="text-teal-700">EDIT</span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Edit Unit / Plat</h1>
        <p class="mt-0.5 text-xs text-slate-500">Perbarui tipe, plat, status operasional, atau catatan unit.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs sm:p-6">
        @include('admin.units._form')
    </div>

    <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5">
        <h2 class="text-sm font-extrabold text-rose-900">Hapus Unit</h2>
        <p class="mt-1 text-xs leading-relaxed text-rose-700">
            Unit yang pernah dipakai pada trip atau booking tidak dapat dihapus. Gunakan status Servis untuk mengeluarkannya dari ketersediaan.
        </p>
        <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus unit ini? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-rose-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Unit
            </button>
        </form>
    </div>
</div>
@endsection

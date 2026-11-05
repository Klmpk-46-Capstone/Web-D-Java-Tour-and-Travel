@extends('layouts.admin')

@section('title', 'Tambah Unit Armada')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <div class="mb-1 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
            <a href="{{ route('admin.units.index') }}" class="hover:text-teal-700">UNIT &amp; PLAT</a>
            <span>/</span>
            <span class="text-teal-700">TAMBAH</span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Tambah Unit / Plat</h1>
        <p class="mt-0.5 text-xs text-slate-500">Daftarkan unit fisik armada beserta plat nomornya.</p>
    </div>

    <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs sm:p-6">
        @include('admin.units._form')
    </div>
</div>
@endsection

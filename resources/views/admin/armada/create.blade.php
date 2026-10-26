@extends('layouts.admin')

@section('title', 'Tambah Armada Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.armada.index') }}" class="hover:text-teal-700">ARMADA</a>
                <span>/</span>
                <span class="text-teal-700">BUAT BARU</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Tambah Armada Baru
            </h1>
        </div>

        <a href="{{ route('admin.armada.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition shrink-0">
            &larr; Kembali
        </a>
    </div>

    @include('admin.armada._form', [
        'action' => route('admin.armada.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan & Publikasikan Armada',
        'cancelUrl' => route('admin.armada.index'),
    ])

</div>
@endsection

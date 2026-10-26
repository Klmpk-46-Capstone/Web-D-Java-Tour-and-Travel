@extends('layouts.admin')

@section('title', 'Edit Armada ' . $vehicle->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.armada.index') }}" class="hover:text-teal-700">ARMADA</a>
                <span>/</span>
                <span class="text-teal-700">EDIT</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Edit Armada: {{ $vehicle->name }}
            </h1>
        </div>

        <a href="{{ route('admin.armada.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition shrink-0">
            &larr; Kembali
        </a>
    </div>

    @include('admin.armada._form', [
        'action' => route('admin.armada.update', $vehicle->id),
        'method' => 'PUT',
        'submitLabel' => 'Perbarui Armada',
        'cancelUrl' => route('admin.armada.index'),
    ])

</div>
@endsection

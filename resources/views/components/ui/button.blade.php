@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-bold no-underline transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-teal-700 text-white shadow-sm hover:bg-teal-800',
        'secondary' => 'bg-indigo-50 text-slate-700 hover:bg-teal-700 hover:text-white',
        'outline' => 'border border-slate-200 bg-white text-slate-700 hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800',
        'ghost' => 'bg-transparent text-teal-700 hover:bg-teal-50',
        'ghost-danger' => 'bg-transparent text-rose-600 hover:bg-rose-50',
        'whatsapp' => 'bg-green-500 text-white shadow-sm hover:bg-green-600',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700',
    ];

    $sizes = [
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-sm',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

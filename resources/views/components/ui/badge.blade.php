@props([
    'variant' => 'neutral',
    'size' => 'sm',
])

@php
    $variants = [
        'brand' => 'bg-teal-50 text-teal-800 ring-1 ring-inset ring-teal-100',
        'solid' => 'bg-teal-600 text-white shadow-sm',
        'neutral' => 'bg-slate-100 text-slate-600',
        'info' => 'bg-blue-100 text-blue-900',
        'warning' => 'bg-amber-100 text-amber-900',
        'success' => 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-100',
        'danger' => 'bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-100',
        'dark' => 'bg-slate-900 text-white',
    ];

    $sizes = [
        'xs' => 'px-2.5 py-0.5 text-[11px]',
        'sm' => 'px-3 py-1 text-xs',
        'md' => 'px-3.5 py-1.5 text-sm',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full font-bold '
        . ($variants[$variant] ?? $variants['neutral'])
        . ' '
        . ($sizes[$size] ?? $sizes['sm']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

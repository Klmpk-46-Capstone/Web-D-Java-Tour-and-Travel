@props([
    'as' => 'div',
    'tone' => 'white',
    'padding' => 'none',
    'interactive' => false,
])

@php
    $tones = [
        'white' => 'border border-slate-200 bg-white shadow-sm',
        'muted' => 'bg-blue-50',
        'subtle' => 'border border-slate-100 bg-slate-50',
        'transparent' => 'bg-transparent',
    ];

    $paddings = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-5',
        'lg' => 'p-6 md:p-8',
    ];

    $classes = 'rounded-3xl '
        . ($tones[$tone] ?? $tones['white'])
        . ' '
        . ($paddings[$padding] ?? '')
        . ($interactive ? ' transition duration-300 hover:-translate-y-1 hover:shadow-lg' : '');
@endphp

<{{ $as }} {{ $attributes->merge(['class' => trim($classes)]) }}>
    {{ $slot }}
</{{ $as }}>

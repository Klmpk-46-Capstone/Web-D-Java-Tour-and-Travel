@props(['width' => 'wide'])

@php
    $widths = [
        'wide' => 'max-w-screen-2xl',
        'content' => 'max-w-5xl',
        'narrow' => 'max-w-3xl',
    ];
@endphp

<div {{ $attributes->merge([
    'class' => 'mx-auto w-full ' . ($widths[$width] ?? $widths['wide']) . ' px-4 sm:px-6 lg:px-8',
]) }}>
    {{ $slot }}
</div>

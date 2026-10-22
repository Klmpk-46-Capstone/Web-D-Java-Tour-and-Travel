@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'level' => 2,
])

@php
    $tag = 'h' . (int) $level;
@endphp

<div {{ $attributes->merge(['class' => 'mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="max-w-3xl">
        @if ($eyebrow)
            <span class="mb-1 block text-xs font-extrabold uppercase tracking-wider text-teal-700">
                {{ $eyebrow }}
            </span>
        @endif

        <{{ $tag }} class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
            {{ $title }}
        </{{ $tag }}>

        @if ($description)
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                {{ $description }}
            </p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="flex shrink-0 items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

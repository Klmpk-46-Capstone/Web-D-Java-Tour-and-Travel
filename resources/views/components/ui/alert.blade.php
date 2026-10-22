@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => true,
])

@php
    $variants = [
        'info' => [
            'class' => 'border-blue-200 bg-blue-50 text-blue-950',
            'title' => 'text-blue-900',
            'icon' => 'i',
            'iconClass' => 'bg-blue-100 text-blue-700',
        ],
        'success' => [
            'class' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'title' => 'text-emerald-900',
            'icon' => '✓',
            'iconClass' => 'bg-emerald-100 text-emerald-700',
        ],
        'warning' => [
            'class' => 'border-amber-200 bg-amber-50 text-amber-950',
            'title' => 'text-amber-900',
            'icon' => '!',
            'iconClass' => 'bg-amber-100 text-amber-700',
        ],
        'danger' => [
            'class' => 'border-rose-200 bg-rose-50 text-rose-950',
            'title' => 'text-rose-900',
            'icon' => '×',
            'iconClass' => 'bg-rose-100 text-rose-700',
        ],
    ];

    $style = $variants[$variant] ?? $variants['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-3 rounded-2xl border p-4 text-sm shadow-sm ' . $style['class']]) }}>
    <div class="flex items-start gap-3">
        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-sm font-extrabold {{ $style['iconClass'] }}" aria-hidden="true">
            {{ $style['icon'] }}
        </span>

        <div>
            @if ($title)
                <p class="m-0 font-extrabold {{ $style['title'] }}">{{ $title }}</p>
            @endif

            <div class="leading-relaxed {{ $title ? 'mt-0.5' : '' }}">
                {{ $slot }}
            </div>
        </div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            onclick="this.parentElement.remove()"
            class="shrink-0 text-xl font-bold leading-none opacity-60 transition hover:opacity-100"
            aria-label="Tutup notifikasi"
        >
            &times;
        </button>
    @endif
</div>

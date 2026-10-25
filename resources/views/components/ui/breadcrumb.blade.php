@props(['items' => []])

@php
    $back = $items[0] ?? null;
    $trail = array_slice($items, 1);
@endphp

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500']) }}>
    @if ($back)
        <a href="{{ $back['url'] ?? '#' }}" class="font-medium transition hover:text-teal-700">
            <span aria-hidden="true">&larr;</span> {{ $back['label'] ?? (app()->getLocale() === 'en' ? 'Back' : 'Kembali') }}
        </a>
    @endif

    @foreach ($trail as $item)
        <span aria-hidden="true">/</span>

        @if (! empty($item['url']))
            <a href="{{ $item['url'] }}" class="transition hover:text-teal-700">
                {{ $item['label'] }}
            </a>
        @else
            <span class="font-medium text-slate-700" aria-current="page">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>

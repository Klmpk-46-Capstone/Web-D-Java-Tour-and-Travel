@props([
    'label',
    'value',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-100 bg-white p-3']) }}>
    <span class="block text-[11px] font-bold uppercase tracking-wide text-slate-400">
        {{ $label }}
    </span>

    <span class="mt-1 block text-sm font-extrabold leading-snug text-slate-900">
        {{ $value }}
    </span>

    @if ($hint)
        <span class="mt-0.5 block text-[11px] text-slate-500">{{ $hint }}</span>
    @endif
</div>

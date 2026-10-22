@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500']) }}>
    <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-50 text-xl text-slate-400" aria-hidden="true">
        ⌕
    </span>

    <strong class="mb-1 block text-lg font-bold text-slate-800">{{ $title }}</strong>

    @if ($description)
        <p class="mx-auto max-w-md leading-relaxed">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

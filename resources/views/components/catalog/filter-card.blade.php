@props(['action'])

<div {{ $attributes->merge(['class' => 'mb-6 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:p-6']) }}>
    <form
        action="{{ $action }}"
        method="GET"
        class="grid grid-cols-1 items-end gap-3 md:grid-cols-2 xl:grid-cols-12"
    >
        {{ $slot }}
    </form>
</div>

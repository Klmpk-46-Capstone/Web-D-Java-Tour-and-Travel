@props([
    'name',
    'label',
    'value' => null,
    'placeholder' => '',
    'type' => 'text',
    'span' => 'xl:col-span-4',
])

<div class="{{ $span }}">
    <label
        for="{{ $name }}"
        class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-slate-500"
    >
        {{ $label }}
    </label>

    <input
        id="{{ $name }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        maxlength="150"
        {{ $attributes->merge([
            'class' => 'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-teal-700 focus:bg-white focus:outline-none',
        ]) }}
    >
</div>

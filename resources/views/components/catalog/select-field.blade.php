@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'span' => 'xl:col-span-2',
    'autoSubmit' => true,
])

<div class="{{ $span }}">
    <label
        for="{{ $name }}"
        class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-slate-500"
    >
        {{ $label }}
    </label>

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($autoSubmit) onchange="this.form.submit()" @endif
        {{ $attributes->merge([
            'class' => 'w-full cursor-pointer rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 transition focus:border-teal-700 focus:bg-white focus:outline-none',
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
</div>

@props([
    'action',
    'sort' => 'rekomendasi',
    'options' => [],
])

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="m-0 text-sm text-slate-500">
        {{ $slot }}
    </p>

    <form action="{{ $action }}" method="GET" class="flex items-center gap-2">
        @foreach (request()->except(['urutkan', 'page']) as $key => $value)
            @continue(! is_scalar($value) || $value === null || $value === '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <label
            for="catalog-sort"
            class="whitespace-nowrap text-xs font-bold uppercase tracking-wider text-slate-400"
        >
            Urutkan
        </label>

        <select
            id="catalog-sort"
            name="urutkan"
            onchange="this.form.submit()"
            class="cursor-pointer rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition focus:border-teal-700 focus:outline-none"
        >
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($sort === $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    </form>
</div>

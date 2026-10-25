<div class="flex flex-col gap-4 border-b border-blue-100 pb-5 lg:flex-row lg:items-center lg:justify-between">
    <div class="flex flex-wrap items-center gap-2">
        @php
            $filterItems = [
                'all' => ['label' => 'All', 'count' => $counts['all'] ?? 0],
                'waiting_dp' => ['label' => 'Waiting for DP', 'count' => $counts['waiting_dp'] ?? 0],
                'confirmed' => ['label' => 'Confirmed', 'count' => $counts['confirmed'] ?? 0],
                'completed' => ['label' => 'Completed', 'count' => $counts['completed'] ?? 0],
            ];
        @endphp

        @foreach ($filterItems as $key => $item)
            <a
                href="{{ localized_route('customer.bookings.index', array_filter([
                    'status' => $key === 'all' ? null : $key,
                    'search' => $search ?: null,
                ])) }}"
                @class([
                    'inline-flex min-h-11 items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                    'bg-teal-700 text-white shadow-sm' => $activeStatus === $key,
                    'bg-white text-slate-600 hover:bg-teal-50' => $activeStatus !== $key,
                ])
            >
                <span>{{ $item['label'] }}</span>
                <span
                    @class([
                        'rounded-full px-2 py-0.5 text-xs font-bold',
                        'bg-white/20 text-white' => $activeStatus === $key,
                        'bg-orange-100 text-orange-800' => $activeStatus !== $key && $key === 'waiting_dp',
                        'bg-teal-100 text-teal-800' => $activeStatus !== $key && $key === 'confirmed',
                        'bg-blue-100 text-blue-800' => $activeStatus !== $key && $key === 'completed',
                        'bg-slate-100 text-slate-700' => $activeStatus !== $key && $key === 'all',
                    ])
                >
                    {{ $item['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <form
        action="{{ localized_route('customer.bookings.index') }}"
        method="GET"
        class="flex w-full gap-2 lg:max-w-sm"
        role="search"
    >
        @if ($activeStatus !== 'all')
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif

        <label for="bookingSearch" class="sr-only">Search invoice or package</label>

        <div class="flex min-h-11 flex-1 items-center gap-3 rounded-xl border border-blue-100 bg-white px-3.5 focus-within:border-teal-600">
            <svg
                class="h-5 w-5 shrink-0 text-slate-600"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7"></circle>
                <path d="m20 20-4-4"></path>
            </svg>

            <input
                id="bookingSearch"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Search invoice or package..."
                maxlength="100"
                class="w-full border-0 bg-transparent text-sm text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0"
            >
        </div>

        <button
            type="submit"
            class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800"
        >
            Search
        </button>
    </form>
</div>

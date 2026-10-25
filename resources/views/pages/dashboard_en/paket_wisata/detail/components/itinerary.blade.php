{{-- Itinerary --}}
<section>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">
            Travel Plan (Itinerary)
        </h2>
        <span class="text-sm text-slate-500">
            {{ $package->itineraries->count() }} trip activities
        </span>
    </div>

    @forelse ($package->itineraries->sortBy('day_number') as $itinerary)
        <article class="mb-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <span class="rounded-md bg-teal-700 px-3 py-1 text-xs font-bold uppercase text-white">
                    Day {{ $itinerary->day_number }}
                </span>
                <h3 class="text-lg font-bold text-slate-900 sm:text-xl">
                    {{ $itinerary->title }}
                </h3>
            </div>

            @if ($itinerary->description)
                <p class="leading-7 text-slate-600">
                    {{ $itinerary->description }}
                </p>
            @endif

            @if ($itinerary->activities)
                <div class="mt-4 whitespace-pre-line leading-7 text-slate-600">{{ $itinerary->activities }}</div>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-600">
            The travel plan for this package is not available yet.
        </div>
    @endforelse
</section>

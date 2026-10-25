{{-- ARMADA --}}
@php
    $isSearchResult = $searchActive ?? false;
@endphp

<section class="bg-indigo-50 py-10 pb-14" id="armada">
    <div class="container mx-auto px-4">
        <div class="mb-5 flex flex-col items-start gap-2 md:mb-7 md:flex-row md:items-end md:justify-between md:gap-7">
            <div>
                <span class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-teal-700">{{ $isSearchResult ? 'SEARCH RESULTS' : 'COMFORT STANDARD' }}</span>
                <h2 class="m-0 text-3xl leading-tight tracking-tight md:text-4xl">{{ $isSearchResult ? 'Matching Vehicles' : 'Featured Vehicles' }}</h2>
            </div>
            <div class="max-w-md">
                <p class="mb-3 mt-0 text-sm leading-relaxed text-slate-500 md:mb-2">
                    {{ $isSearchResult
                        ? 'Vehicles that match your keyword and preferred passenger capacity.'
                        : 'Choose a vehicle based on your travel needs, passenger capacity, and destination.' }}
                </p>
                <a class="inline-flex items-center gap-2 text-xs font-bold text-teal-700 no-underline transition hover:text-teal-800" href="{{ localized_route('armada') }}">
                    View all vehicles
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
            @forelse ($vehicles as $vehicle)
                <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="relative h-60 bg-blue-50 p-4 md:h-64 2xl:h-72">
                        <span class="absolute left-4 top-3 z-10 rounded-md border border-teal-100 bg-white px-2 py-1.5 text-xs font-bold text-teal-700">{{ $vehicle->price_without_driver ? 'With / Without Driver' : ($vehicle->includes_driver ? 'With Driver' : 'Self-Drive') }}</span>
                        @php
                            $vehicleImageUrl = $vehicle->image_url;
                        @endphp
                        @if ($vehicleImageUrl)
                            <img class="h-full w-full rounded-lg bg-slate-100 object-contain" src="{{ $vehicleImageUrl }}" alt="{{ $vehicle->name }}" loading="lazy">
                        @else
                            <div class="grid h-full w-full place-items-center rounded-lg px-6 py-10 text-center text-slate-500">Vehicle photo is not available yet.</div>
                        @endif
                    </div>

                    <div class="p-5">
                        <h3 class="mb-1.5 mt-0 text-xl tracking-tight">{{ $vehicle->name }}</h3>
                        <p class="mb-4 mt-0 min-h-12 text-sm leading-relaxed text-slate-500">{{ data_get($vehicle, 'description', 'Vehicle information can be confirmed with the D’Java Tour & Travel team.') }}</p>

                        <div class="flex flex-wrap justify-between gap-2 border-y border-slate-200 py-3 text-xs text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="shrink-0 text-teal-700" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                                {{ data_get($vehicle, 'seats', '—') }} Seats
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="shrink-0 text-teal-700" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="7" width="16" height="14" rx="2"/><path d="M9 7V4h6v3M9 11v6M15 11v6"/></svg>
                                {{ data_get($vehicle, 'luggage_capacity', '—') }} Bags
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="shrink-0 text-teal-700" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                {{ data_get($vehicle, 'transmission', 'Contact us') }}
                            </span>
                        </div>

                        <div class="flex items-end justify-between gap-3 pt-4">
                            <div>
                                <small class="block text-xs font-bold tracking-wide text-slate-400">STARTING FROM</small>
                                @if (data_get($vehicle, 'lowest_price') !== null)
                                    <strong class="text-2xl leading-relaxed tracking-tight">{{ $formatRupiah(data_get($vehicle, 'lowest_price')) }}</strong>
                                    <span class="text-xs text-slate-500">/day</span>
                                @else
                                    <strong class="text-2xl leading-relaxed tracking-tight">Consultation</strong>
                                @endif
                            </div>
                            <a class="inline-flex items-center justify-center rounded-xl bg-indigo-50 px-5 py-2.5 text-xs font-bold text-slate-700 no-underline transition hover:bg-teal-700 hover:text-white" href="{{ localized_route('armada.show', $vehicle->slug) }}">View Details</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-500">
                    <strong class="mb-2 block text-lg text-slate-900">{{ $isSearchResult ? 'No vehicles found' : 'No vehicles available yet' }}</strong>
                    {{ $isSearchResult
                        ? 'Try changing the keyword or passenger capacity.'
                        : 'There are no active vehicles in the database yet. Please add vehicle data first.' }}
                </div>
            @endforelse
        </div>
    </div>
</section>

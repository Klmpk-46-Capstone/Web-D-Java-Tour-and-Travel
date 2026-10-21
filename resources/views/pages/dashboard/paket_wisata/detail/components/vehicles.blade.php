{{-- Vehicle section --}}
@if ($vehicleList->isNotEmpty())
    <section class="rounded-2xl bg-blue-50 p-4 sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">
                Armada Transportasi
            </h2>
            <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-800">
                Armada tersedia
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($vehicleList as $vehicle)
                <article class="overflow-hidden rounded-xl border border-slate-100 bg-white">
                    @php
                        $vehicleImage = $imageUrl($vehicle->image_path ?? null);
                    @endphp

                    @if ($vehicleImage)
                        <img
                            src="{{ $vehicleImage }}"
                            alt="{{ $vehicle->name }}"
                            class="h-48 w-full object-cover"
                            loading="lazy"
                        >
                    @endif

                    <div class="p-4">
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ $vehicle->name }}
                        </h3>

                        @if ($vehicle->description)
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $vehicle->description }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                            <span class="rounded-full bg-slate-100 px-3 py-1">
                                {{ $vehicle->seats }} kursi
                            </span>
                            @if ($vehicle->transmission)
                                <span class="rounded-full bg-slate-100 px-3 py-1">
                                    {{ $vehicle->transmission }}
                                </span>
                            @endif
                            @if ($vehicle->includes_driver)
                                <span class="rounded-full bg-slate-100 px-3 py-1">
                                    Termasuk pengemudi
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif

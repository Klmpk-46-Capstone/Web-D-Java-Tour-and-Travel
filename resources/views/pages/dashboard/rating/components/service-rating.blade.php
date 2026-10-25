<section>
    <h2 class="text-xl font-bold text-slate-900">
        Penilaian Detail Aspek Layanan
    </h2>

    <p class="mt-2 text-sm text-slate-600">
        Bantu kami meningkatkan kualitas pelayanan.
    </p>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $serviceItems = [
                'armada' => [
                    'title' => 'Kondisi Armada',
                    'description' => 'Kebersihan, kenyamanan, dan AC.',
                ],
                'driver' => [
                    'title' => 'Pelayanan Driver',
                    'description' => 'Keramahan, sikap, dan keselamatan.',
                ],
                'route' => [
                    'title' => 'Rute & Ketepatan',
                    'description' => 'Jadwal dan ketepatan perjalanan.',
                ],
            ];
        @endphp

        @foreach ($serviceItems as $key => $item)
            <div
                id="service-card-{{ $key }}"
                class="rounded-xl border border-transparent bg-slate-50 p-4 transition"
            >
                <h3 class="font-semibold text-slate-900">
                    {{ $item['title'] }}
                </h3>

                <p class="mt-1 text-sm text-slate-600">
                    {{ $item['description'] }}
                </p>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex gap-1" role="group" aria-label="{{ $item['title'] }}">
                        @foreach (range(1, 5) as $star)
                            <button
                                type="button"
                                data-service="{{ $key }}"
                                data-value="{{ $star }}"
                                class="service-star text-2xl leading-none text-slate-300 transition hover:scale-110 hover:text-orange-400 focus:outline-none focus:ring-2 focus:ring-teal-600"
                                aria-label="{{ $item['title'] }}: {{ $star }} dari 5"
                                aria-pressed="false"
                            >
                                ★
                            </button>
                        @endforeach
                    </div>

                    <span
                        id="service-label-{{ $key }}"
                        class="text-sm font-semibold text-slate-600"
                    >
                        {{ old('service_ratings.' . $key) ? number_format((float)old('service_ratings.' . $key), 1, '.', '') : 'Belum dinilai' }}
                    </span>
                </div>

                <input
                    type="hidden"
                    name="service_ratings[{{ $key }}]"
                    id="service-value-{{ $key }}"
                    value="{{ old('service_ratings.' . $key, '') }}"
                >

                <p
                    id="service-error-{{ $key }}"
                    class="mt-2 hidden text-sm text-red-600"
                    aria-live="polite"
                >
                    Silakan beri rating untuk {{ strtolower($item['title']) }}.
                </p>

                @error('service_ratings.' . $key)
                    <p class="mt-2 text-sm text-red-600">
                        Silakan beri rating untuk {{ strtolower($item['title']) }}.
                    </p>
                @enderror
            </div>
        @endforeach
    </div>
</section>

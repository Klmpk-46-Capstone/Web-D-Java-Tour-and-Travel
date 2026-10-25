<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $vehicle->name }} - Vehicle Rental | D'Java Tour &amp; Travel</title>
    <meta name="description" content="{{ $vehicle->description ?: 'Vehicle unit details and rental options from D’Java Tour & Travel.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/armada-detail.js'])

    <style>
        body {
            font-family: 'DM Sans', Arial, sans-serif;
        }

        #passengerCount::-webkit-outer-spin-button,
        #passengerCount::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        #passengerCount {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        .flatpickr-calendar {
            font-family: 'DM Sans', Arial, sans-serif;
            border-radius: 1rem;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        }

        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected:hover {
            background: #0f766e;
            border-color: #0f766e;
        }

        .flatpickr-day.today {
            border-color: #0f766e;
        }

        .flatpickr-months .flatpickr-month,
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            font-weight: 700;
        }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">

    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, '.', ',');

        $whatsappNumber = preg_replace('/\D+/', '', (string) config('services.djava.whatsapp', ''));

        $options = collect($vehicle->rentalOptions())
            ->map(function (array $option) use ($formatRupiah) {
                $option['formatted_price'] = $formatRupiah($option['price']);

                return $option;
            })
            ->values()
            ->all();

        $defaultOption = $options[0] ?? null;
        $downPaymentRate = 30;
        $initialDpPerUnit = $defaultOption
            ? (int) round($defaultOption['price'] * $downPaymentRate / 100)
            : 0;
        $csMessage = "Hello D'Java Tour & Travel, I would like to ask about the schedule and availability of {$vehicle->name}.";
        $csWhatsappUrl = $whatsappNumber !== ''
            ? 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($csMessage)
            : localized_route('home') . '#kontak';

        $includes = [
            'Vehicle unit according to the selected rental option',
            'Clean unit with regular servicing',
            'Emergency assistance during the rental period',
            'Route and travel needs consultation',
        ];

        if ($vehicle->includes_driver) {
            $includes[] = 'Professional driver for the with-driver option';
        }

        $excludes = [
            'Fuel, tolls, and parking according to the rental agreement',
            'Tour tickets and destination entrance fees',
            'Accommodation and meals during the trip',
            'Overtime charges beyond the rental duration',
        ];
    @endphp

    @include('components.navbar.navbar')

    <main class="flex-1 py-6 md:py-8">
        <x-ui.page-container>

            {{-- Breadcrumb --}}
            <x-ui.breadcrumb :items="[
                ['label' => 'Back to Vehicles', 'url' => localized_route('armada')],
                ['label' => 'Vehicles', 'url' => localized_route('armada')],
                ['label' => $vehicle->name],
            ]" />

            {{-- Judul unit --}}
            <section class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-4xl">
                    @if ($vehicle->badge)
                        <x-ui.badge variant="brand" size="md" class="mb-3 uppercase tracking-wide">
                            {{ $vehicle->badge }}
                        </x-ui.badge>
                    @endif

                    <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        {{ $vehicle->name }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
                        {{ $vehicle->description ?: 'Vehicle unit information and rental rates are available on this page.' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 text-sm">
                    <x-ui.badge variant="info">
                        <span aria-hidden="true">◷</span>
                        {{ $vehicle->seats }} seats
                    </x-ui.badge>

                    <x-ui.badge variant="info">
                        <span aria-hidden="true">⌖</span>
                        {{ $vehicle->category_label }}
                    </x-ui.badge>

                    <x-ui.badge variant="neutral">
                        {{ $vehicle->transmission ?: 'Transmission TBA' }}
                    </x-ui.badge>

                    <x-ui.badge variant="warning">
                        {{ $vehicle->luggage_capacity }} bags
                    </x-ui.badge>
                </div>
            </section>

            {{-- Konten utama dan panel tarif --}}
            <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">

                {{-- Kolom kiri: foto, spesifikasi, fasilitas, armada terkait (8 kolom) --}}
                <div class="space-y-6 xl:col-span-8">

                    {{-- Foto unit --}}
                    <x-ui.card padding="none" class="overflow-hidden">
                        <div class="relative h-64 bg-slate-200 sm:h-80 lg:h-[26rem]">
                            @if ($vehicle->image_url)
                                <img
                                    src="{{ $vehicle->image_url }}"
                                    alt="{{ $vehicle->name }}"
                                    class="h-full w-full object-cover"
                                    loading="eager"
                                    decoding="async"
                                >
                            @else
                                <div class="grid h-full place-items-center px-6 text-center text-sm text-slate-500">
                                    Vehicle photo is not available yet.
                                </div>
                            @endif

                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/50 to-transparent"></div>

                            <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-center gap-2">
                                <x-ui.badge variant="dark">{{ $vehicle->category_label }}</x-ui.badge>
                                <x-ui.badge variant="solid">{{ $vehicle->seats }} seats</x-ui.badge>
                                @if ($vehicle->includes_driver)
                                    <x-ui.badge variant="success">Driver option available</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Spesifikasi --}}
                    <x-ui.card tone="muted" padding="lg">
                        <x-ui.section-heading
                            title="Unit Specifications"
                            description="Capacity and comfort for your travel needs."
                        />

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <x-ui.stat-tile label="Capacity" :value="$vehicle->seats . ' seats'" />
                            <x-ui.stat-tile label="Luggage" :value="$vehicle->luggage_capacity . ' bags'" />
                            <x-ui.stat-tile label="Transmission" :value="$vehicle->transmission ?: 'Contact us'" />
                            <x-ui.stat-tile label="Category" :value="$vehicle->category_label" />
                            <x-ui.stat-tile label="Driver Option" :value="$vehicle->includes_driver ? 'Available' : 'Not available'" />
                            <x-ui.stat-tile label="Starting Rate" :value="($vehicle->formatted_lowest_price ?? $vehicle->formatted_price) . ' /day'" />
                        </div>
                    </x-ui.card>

                    {{-- Termasuk dan tidak termasuk --}}
                    <x-ui.card tone="muted" padding="lg">
                        <x-ui.section-heading
                            title="Included & Not Included"
                            description="Standard service details. Final terms are confirmed at booking."
                        />

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-100 bg-white p-4">
                                <h3 class="mb-3 flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                    <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-100 text-xs font-black text-emerald-700" aria-hidden="true">✓</span>
                                    Included
                                </h3>

                                <ul class="m-0 list-none space-y-2 p-0 text-sm text-slate-600">
                                    @foreach ($includes as $item)
                                        <li class="flex items-start gap-2">
                                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-white p-4">
                                <h3 class="mb-3 flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                    <span class="grid h-6 w-6 place-items-center rounded-full bg-rose-100 text-xs font-black text-rose-700" aria-hidden="true">×</span>
                                    Not Included
                                </h3>

                                <ul class="m-0 list-none space-y-2 p-0 text-sm text-slate-600">
                                    @foreach ($excludes as $item)
                                        <li class="flex items-start gap-2">
                                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400" aria-hidden="true"></span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Customer reviews --}}
                    @if ($reviews->isNotEmpty())
                        <section aria-labelledby="vehicle-reviews">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 id="vehicle-reviews" class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                                        Customer Reviews
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Reviews from customers who have completed their rental.
                                    </p>
                                </div>

                                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900">
                                    ★ {{ number_format((float) $averageRating, 1) }} / 5
                                </span>
                            </div>

                            <div class="space-y-4">
                                @foreach ($reviews as $review)
                                    <x-reviews.card :review="$review" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Armada terkait --}}
                    @if ($relatedVehicles->isNotEmpty())
                        <section aria-labelledby="armada-terkait">
                            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                                <div>
                                    <h2 id="armada-terkait" class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                                        Other Vehicles
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Other units that could be an alternative for your trip.
                                    </p>
                                </div>

                                <x-ui.button href="{{ localized_route('armada') }}" variant="ghost" size="sm">
                                    View all vehicles
                                    <span aria-hidden="true">&rarr;</span>
                                </x-ui.button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($relatedVehicles as $related)
                                    <x-ui.card as="article" padding="none" interactive class="overflow-hidden">
                                        <div class="relative h-36 bg-slate-100">
                                            @if ($related->image_url)
                                                <img
                                                    src="{{ $related->image_url }}"
                                                    alt="{{ $related->name }}"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                    decoding="async"
                                                >
                                            @else
                                                <div class="grid h-full place-items-center text-xs text-slate-500">
                                                    Photo is not available yet
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-4">
                                            <h3 class="text-sm font-extrabold leading-snug text-slate-900">
                                                {{ $related->name }}
                                            </h3>

                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $related->category_label }} · {{ $related->seats }} seats
                                            </p>

                                            <div class="mt-3 flex items-center justify-between gap-2">
                                                <div>
                                                    <span class="block text-[11px] font-bold uppercase tracking-wide text-slate-400">
                                                        Starting from
                                                    </span>
                                                    <span class="text-sm font-black text-teal-700">{{ $related->formatted_lowest_price ?? $related->formatted_price }}</span>
                                                    <span class="text-xs text-slate-500">/day</span>
                                                </div>

                                                <x-ui.button href="{{ localized_route('armada.show', $related->slug) }}" variant="outline" size="sm">
                                                    Details
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </x-ui.card>
                                @endforeach
                            </div>
                        </section>
                    @endif

                </div>

                {{-- Kolom kanan: panel tarif, opsi sewa, dan ringkasan booking (4 kolom, sticky) --}}
                <aside class="w-full space-y-4 xl:sticky xl:top-24 xl:self-start xl:col-span-4">

                    <section
                        id="armadaBookingCard"
                        class="min-w-0 rounded-2xl bg-white p-5 shadow-md ring-1 ring-slate-100 sm:p-6"
                        data-armada-booking
                        data-seats="{{ $vehicle->seats }}"
                        data-vehicle-name="{{ $vehicle->name }}"
                        data-wa-number="{{ $whatsappNumber }}"
                        data-contact-url="{{ localized_route('home') }}#kontak"
                        data-booking-form-url="{{ localized_route('armada.booking.form', $vehicle) }}"
                    >

                        {{-- TARIF UNIT --}}
                        <div class="mb-5 min-w-0">
                            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500">
                                Rental Rate
                            </p>

                            <div class="mt-1 flex flex-wrap items-end justify-between gap-x-3 gap-y-1">
                                <p class="m-0 min-w-0 break-words text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl">
                                    <span id="rentalPrice">{{ $defaultOption['formatted_price'] ?? $vehicle->formatted_price }}</span>
                                </p>

                                <span class="mb-1 shrink-0 text-sm text-slate-500">/day</span>
                            </div>

                            <p class="mt-2 text-xs font-semibold leading-relaxed text-teal-700">
                                Option:
                                <span id="rentalOptionLabel">{{ $defaultOption['label'] ?? 'Daily Rental' }}</span>
                            </p>
                        </div>

                        {{-- INFORMASI DP --}}
                        <div class="mb-5 rounded-xl bg-blue-50 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="m-0 min-w-0 text-sm font-medium leading-snug text-teal-800">
                                    Low Down Payment (DP):
                                </p>

                                <span class="shrink-0 rounded-full bg-teal-200 px-2 py-1 text-xs font-bold text-teal-800">
                                    {{ $downPaymentRate }}%
                                </span>
                            </div>

                            <div class="mt-1 flex flex-wrap items-baseline gap-x-1 gap-y-0.5">
                                <span id="dpPerUnit" class="min-w-0 break-words text-2xl font-extrabold text-teal-700">
                                    {{ $formatRupiah($initialDpPerUnit) }}
                                </span>

                                <span class="text-xs text-slate-600">/ unit</span>
                            </div>

                            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                Secure your rental schedule by paying a down payment.
                                The remaining balance is paid according to the terms.
                            </p>
                        </div>

                        {{-- OPSI SEWA --}}
                        <div class="mb-4 min-w-0">
                            <p class="mb-2 text-sm font-semibold text-slate-800">
                                Rental Options
                            </p>

                            @if (count($options) > 0)
                                <div class="grid grid-cols-1 gap-1 rounded-xl bg-blue-50 p-1 sm:grid-cols-2">
                                    @foreach ($options as $option)
                                        <button
                                            type="button"
                                            data-rental-option
                                            data-key="{{ $option['key'] }}"
                                            data-label="{{ $option['label'] }}"
                                            data-price="{{ $option['price'] }}"
                                            data-description="{{ $option['description'] }}"
                                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                            class="rental-option-btn flex min-h-11 min-w-0 items-center justify-center rounded-lg px-2 py-2.5 text-center text-xs font-bold leading-snug transition hover:bg-white/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 {{ $loop->first ? 'bg-white text-teal-800 shadow-sm' : 'text-slate-600' }}"
                                        >
                                            <span class="min-w-0 break-words">{{ $option['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>

                                <p id="rentalOptionDescription" class="mt-2 text-xs leading-relaxed text-slate-500">
                                    {{ $defaultOption['description'] ?? '' }}
                                </p>
                            @else
                                <p class="m-0 rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">
                                    Rental options are not available yet. Please contact CS.
                                </p>
                            @endif
                        </div>

                        {{-- TANGGAL SEWA --}}
                        <div class="mb-4 min-w-0">
                            <label for="rentalDate" class="mb-2 block text-sm font-semibold text-slate-800">
                                Rental Date
                            </label>

                            <div class="relative">
                                <input
                                    id="rentalDate"
                                    name="rental_date"
                                    type="text"
                                    placeholder="Select rental date"
                                    autocomplete="off"
                                    class="w-full min-w-0 rounded-xl border-0 bg-blue-50 py-3 pl-4 pr-11 text-sm text-slate-700 outline-none ring-1 ring-blue-100 placeholder:text-slate-400 focus:ring-2 focus:ring-teal-600"
                                >

                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-teal-700" aria-hidden="true">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        <path d="M16 2v4M8 2v4M3 10h18"/>
                                    </svg>
                                </span>
                            </div>

                            <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                                Select the rental start date.
                            </p>
                        </div>

                        {{-- JUMLAH PENUMPANG --}}
                        <div class="mb-4 min-w-0">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <label for="passengerCount" class="text-sm font-semibold text-slate-800">
                                    Number of Passengers
                                </label>

                                <span class="shrink-0 rounded-full border border-teal-200 bg-teal-50 px-2.5 py-0.5 text-xs font-bold text-teal-800">
                                    Max {{ $vehicle->seats }} Pax
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-2 rounded-xl bg-blue-50 p-2">
                                <button
                                    type="button"
                                    id="decreasePax"
                                    aria-label="Decrease passengers"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    &minus;
                                </button>

                                <input
                                    id="passengerCount"
                                    name="pax"
                                    type="number"
                                    min="0"
                                    max="{{ $vehicle->seats }}"
                                    value="0"
                                    inputmode="numeric"
                                    class="min-w-0 w-16 border-0 bg-transparent text-center text-base font-bold text-slate-800 outline-none focus:ring-0"
                                >

                                <button
                                    type="button"
                                    id="increasePax"
                                    aria-label="Increase passengers"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    +
                                </button>
                            </div>

                            <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                                Minimum 1 passenger to continue booking.
                            </p>
                        </div>

                        {{-- RINGKASAN BIAYA --}}
                        <div class="rounded-xl border border-slate-100 p-4">
                            <div class="flex items-start justify-between gap-3 text-sm">
                                <span class="min-w-0 break-words text-slate-600">
                                    Total Rental (<span id="summaryPax">0</span> Pax)
                                </span>

                                <strong id="displayTotal" class="shrink-0 whitespace-nowrap text-right text-slate-900" aria-live="polite">
                                    Rp 0
                                </strong>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                                <span class="shrink-0 font-semibold text-teal-700">
                                    DP ({{ $downPaymentRate }}%)
                                </span>

                                <strong id="displayDP" class="shrink-0 whitespace-nowrap text-right text-teal-700">
                                    Rp 0
                                </strong>
                            </div>

                            <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                <span class="shrink-0 text-slate-500">
                                    Remaining Balance
                                </span>

                                <span id="displayRemaining" class="shrink-0 whitespace-nowrap text-right font-medium text-slate-500">
                                    Rp 0
                                </span>
                            </div>
                        </div>

                        {{-- CTA --}}
                        <a
                            id="continueToBookingForm"
                            href="{{ localized_route('armada.booking.form', ['vehicle' => $vehicle->slug, 'rental_option' => $defaultOption['key'] ?? null, 'duration' => 1]) }}"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-4 text-sm font-bold text-white shadow-sm transition hover:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-700 focus:ring-offset-2 sm:text-base"
                        >
                            <span class="min-w-0 text-center leading-snug">Continue to Booking Form</span>
                            <span class="shrink-0" aria-hidden="true">&rarr;</span>
                        </a>

                        <div id="bookingFormError" class="mt-2 hidden rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs leading-relaxed text-rose-700" role="alert"></div>

                        <a
                            id="rentalCsCta"
                            href="{{ $csWhatsappUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-2 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:bg-blue-100"
                        >
                            <span class="shrink-0 text-teal-700" aria-hidden="true">▣</span>
                            <span class="min-w-0 text-center leading-snug">Ask About Schedules via WhatsApp CS</span>
                        </a>

                        <div class="mt-3 flex items-start justify-center gap-2 text-center text-xs leading-relaxed text-slate-500">
                            <span class="mt-0.5 shrink-0 text-teal-700" aria-hidden="true">✓</span>
                            <p class="m-0">
                                Unit availability confirmation &bull; Transparent pricing &bull; No hidden fees
                            </p>
                        </div>
                    </section>

                    <x-ui.card tone="muted" padding="md" class="min-w-0">
                        <p class="text-sm font-extrabold text-slate-900">Need help choosing a unit?</p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">
                            Our team can help adjust capacity, route, and rental duration.
                        </p>

                        <x-ui.button
                            href="{{ $csWhatsappUrl }}"
                            variant="outline"
                            size="sm"
                            class="mt-3 w-full"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Contact Our Team
                        </x-ui.button>
                    </x-ui.card>

                </aside>

            </div>

        </x-ui.page-container>
    </main>

    @include('components.footer.footer')

</body>

</html>

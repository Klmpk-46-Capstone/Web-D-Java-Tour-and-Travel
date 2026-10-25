{{-- resources/views/pages/dashboard/armada/formulir/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vehicle Booking Form - D'Java Tour &amp; Travel</title>
    <meta name="description" content="D'Java Tour & Travel vehicle rental reservation form.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/armada-booking-form.js'])
    <style>
        body { font-family: 'DM Sans', Arial, sans-serif; }

        #passengerCount::-webkit-outer-spin-button,
        #passengerCount::-webkit-inner-spin-button,
        #rentalDuration::-webkit-outer-spin-button,
        #rentalDuration::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        #passengerCount,
        #rentalDuration {
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
    </style>
</head>
<body class="min-h-screen bg-indigo-50/50 text-slate-800 antialiased">
    @include('components.navbar.navbar')

    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, '.', ',');
        $downPaymentRate = 30;
        $maxDuration = 30;

        $options = collect($options)
            ->map(function (array $option) use ($formatRupiah) {
                $option['formatted_price'] = $formatRupiah($option['price']);

                return $option;
            })
            ->values()
            ->all();

        $selectedOption = collect($options)->firstWhere('key', $selectedOptionKey) ?? ($options[0] ?? null);
        $selectedRate = (int) ($selectedOption['price'] ?? 0);
        $duration = max(1, min((int) $selectedDuration, $maxDuration));
        $pax = max(1, min((int) $selectedPax, max(1, (int) $vehicle->seats)));
        $totalPrice = $selectedRate * $duration;
        $downPayment = (int) round($totalPrice * $downPaymentRate / 100);
        $remainingPayment = $totalPrice - $downPayment;
        $rentalDateLabel = $selectedRentalDate
            ? \Illuminate\Support\Carbon::parse($selectedRentalDate)->translatedFormat('d F Y')
            : 'Select date';
    @endphp

    <main class="mx-auto w-full max-w-screen-2xl px-4 py-7 sm:px-6 lg:px-8 xl:py-8">
        <header class="mb-8">
            <a href="{{ localized_route('armada.show', $vehicle->slug) }}" class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-teal-700 transition hover:text-teal-900">
                <span aria-hidden="true">&larr;</span> Back
            </a>

            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        Reservation &amp; Down Payment Form
                    </h1>
                    <p class="mt-2 max-w-4xl text-base leading-7 text-slate-600 sm:text-lg">
                        Complete your vehicle rental details. The operations team will process unit availability confirmation.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-100 px-4 py-3 text-sm font-bold text-slate-700">
                    <svg class="h-4 w-4 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    Encrypted &amp; Secure Data
                </div>
            </div>
        </header>

        @if (session('error'))
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="mb-2 font-bold">Please check the data you entered.</p>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="armadaReservationForm"
            action="{{ localized_route('armada.booking.store', ['vehicle' => $vehicle->slug]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-8"
            data-seats="{{ $vehicle->seats }}"
            data-max-duration="{{ $maxDuration }}"
        >
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
            <input type="hidden" name="vehicle_slug" value="{{ $vehicle->slug }}">

            <div class="min-w-0 space-y-6 lg:col-span-7">
                <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                    <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-slate-900 sm:text-2xl">
                        <svg class="h-5 w-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 11h18"/>
                        </svg>
                        Schedule &amp; Pickup Location
                    </h2>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="min-w-0">
                            <label for="rentalDate" class="mb-2 block text-sm font-semibold text-slate-700">Rental Start Date</label>
                            <div class="relative">
                                <input
                                    id="rentalDate"
                                    name="rental_date"
                                    type="text"
                                    placeholder="Select rental date"
                                    autocomplete="off"
                                    aria-required="true"
                                    value="{{ $selectedRentalDate }}"
                                    data-default-date="{{ $selectedRentalDate }}"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-blue-50 py-3 pl-4 pr-11 text-base text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-teal-700" aria-hidden="true">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        <path d="M16 2v4M8 2v4M3 10h18"/>
                                    </svg>
                                </span>
                            </div>
                            <p id="rentalDateError" class="mb-0 mt-1.5 hidden text-xs font-semibold text-rose-600" role="alert">
                                Please select a rental start date.
                            </p>
                        </div>

                        <div class="min-w-0">
                            <label for="rentalDuration" class="mb-2 block text-sm font-semibold text-slate-700">Rental Duration</label>
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-blue-50 p-2">
                                <button
                                    type="button"
                                    id="decreaseDuration"
                                    aria-label="Decrease rental duration"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    &minus;
                                </button>

                                <div class="flex min-w-0 items-baseline justify-center gap-1">
                                    <input
                                        id="rentalDuration"
                                        name="duration"
                                        type="number"
                                        min="1"
                                        max="{{ $maxDuration }}"
                                        value="{{ $duration }}"
                                        inputmode="numeric"
                                        required
                                        class="w-12 min-w-0 border-0 bg-transparent p-0 text-center text-base font-bold text-slate-800 outline-none focus:ring-0"
                                    >
                                    <span class="text-sm font-medium text-slate-500">days</span>
                                </div>

                                <button
                                    type="button"
                                    id="increaseDuration"
                                    aria-label="Increase rental duration"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                <label for="passengerCount" class="text-sm font-semibold text-slate-700">Number of Passengers</label>
                                <span class="shrink-0 rounded-full border border-teal-200 bg-teal-50 px-2.5 py-0.5 text-xs font-bold text-teal-800">
                                    Max {{ $vehicle->seats }} Pax
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-blue-50 p-2">
                                <button
                                    type="button"
                                    id="decreasePax"
                                    aria-label="Decrease passengers"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    &minus;
                                </button>

                                <div class="flex min-w-0 items-baseline justify-center gap-1">
                                    <input
                                        id="passengerCount"
                                        name="pax"
                                        type="number"
                                        min="1"
                                        max="{{ $vehicle->seats }}"
                                        value="{{ $pax }}"
                                        inputmode="numeric"
                                        required
                                        class="w-12 min-w-0 border-0 bg-transparent p-0 text-center text-base font-bold text-slate-800 outline-none focus:ring-0"
                                    >
                                    <span class="text-sm font-medium text-slate-500">pax</span>
                                </div>

                                <button
                                    type="button"
                                    id="increasePax"
                                    aria-label="Increase passengers"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xl font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <label for="rentalOption" class="mb-2 block text-sm font-semibold text-slate-700">Rental Option</label>
                            <select
                                id="rentalOption"
                                name="rental_option"
                                required
                                class="w-full min-w-0 rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base text-slate-800 outline-none transition focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                            >
                                @forelse ($options as $option)
                                    <option
                                        value="{{ $option['key'] }}"
                                        data-label="{{ $option['label'] }}"
                                        data-price="{{ $option['price'] }}"
                                        data-description="{{ $option['description'] }}"
                                        data-rental-type="{{ str_contains(strtolower($option['label']), 'without driver') || str_contains(strtolower($option['label']), 'self-drive') ? 'self-drive' : 'with-driver' }}"
                                        @selected($selectedOptionKey === $option['key'])
                                    >
                                        {{ $option['label'] }} — {{ $option['formatted_price'] }}/day
                                    </option>
                                @empty
                                    <option value="">Rental options are not available yet</option>
                                @endforelse
                            </select>
                            <p id="rentalOptionDescription" class="mb-0 mt-2 text-xs leading-relaxed text-slate-500">
                                {{ $selectedOption['description'] ?? 'Choose a suitable rental option.' }}
                            </p>
                        </div>

                        <div class="min-w-0 sm:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Vehicle Selection</label>
                            <div class="flex min-h-12 min-w-0 items-center justify-between gap-3 rounded-xl bg-blue-50 px-4 py-3 text-base text-slate-800">
                                <span class="min-w-0 break-words font-semibold">{{ $vehicle->name }}</span>
                                <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-teal-800">
                                    {{ $vehicle->seats }} seats
                                </span>
                            </div>
                        </div>

                        <div id="vehicleDeliverySection" class="min-w-0 sm:col-span-2 hidden">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Car Delivery Option
                            </label>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-teal-500 has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50">
                                    <input
                                        type="radio"
                                        name="delivery_method"
                                        value="self_pickup"
                                        id="deliverySelfPickup"
                                        class="h-4 w-4 accent-teal-700"
                                        checked
                                    >
                                    <span>
                                        <span class="block font-bold text-slate-800">Self Pickup</span>
                                        <span class="mt-1 block text-sm text-slate-500">
                                            Pick up the car directly at the provider location.
                                        </span>
                                    </span>
                                </label>

                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-teal-500 has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50">
                                    <input
                                        type="radio"
                                        name="delivery_method"
                                        value="delivery"
                                        id="deliveryToAddress"
                                        class="h-4 w-4 accent-teal-700"
                                    >
                                    <span>
                                        <span class="block font-bold text-slate-800">Car Delivery</span>
                                        <span class="mt-1 block text-sm text-slate-500">
                                            The car is delivered to your chosen address.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div id="pickupAddressSection" class="min-w-0 sm:col-span-2 hidden">
                            <label
                                for="pickup_location"
                                id="pickupLocationLabel"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Car Delivery Address
                            </label>

                            <input
                                id="pickup_location"
                                name="pickup_location"
                                type="text"
                                value="{{ old('pickup_location') }}"
                                maxlength="200"
                                autocomplete="street-address"
                                placeholder="Example: Jl. Melati No. 10, Tangerang, Banten"
                                class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                            >

                            <div id="pickupDetailsSection" class="mt-5 hidden">
                                <label
                                    for="pickup_details"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Time Details
                                </label>

                                <textarea
                                    id="pickup_details"
                                    name="pickup_details"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="Example: Car delivered at 08:00 WIB. Please arrive 15 minutes before the scheduled time."
                                    class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                                >{{ old('pickup_details') }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                    <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-slate-900 sm:text-2xl">
                        <svg class="h-5 w-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                            <circle cx="10" cy="13" r="2"/>
                            <path d="M7 18c.8-1.8 5.2-1.8 6 0"/>
                        </svg>
                        Official Booker Details
                    </h2>

                    <div class="space-y-5">
                        <div>
                            <label for="customer_name" class="mb-2 block text-sm font-semibold text-slate-700">Full Name (As on ID / Passport)</label>
                            <input
                                id="customer_name"
                                name="customer_name"
                                type="text"
                                value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                                maxlength="150"
                                autocomplete="name"
                                required
                                placeholder="Enter the group leader's name"
                                class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="min-w-0">
                                <label for="customer_phone" class="mb-2 block text-sm font-semibold text-slate-700">Active WhatsApp Number</label>
                                <input
                                    id="customer_phone"
                                    name="customer_phone"
                                    type="tel"
                                    value="{{ old('customer_phone', auth()->user()->whatsapp ?? '') }}"
                                    maxlength="50"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    required
                                    placeholder="+62 812-XXXX-XXXX"
                                    class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                                >
                            </div>

                            <div class="min-w-0">
                                <label for="customer_email" class="mb-2 block text-sm font-semibold text-slate-700">Email Address (E-Ticket &amp; Invoice)</label>
                                <input
                                    id="customer_email"
                                    name="customer_email"
                                    type="email"
                                    value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                    maxlength="150"
                                    autocomplete="email"
                                    required
                                    placeholder="name@domain.com"
                                    class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="identity_file" class="mb-2 block text-sm font-semibold text-slate-700">Upload ID of the Person in Charge (Optional)</label>

                            <div class="rounded-xl border border-dashed border-blue-200 bg-blue-50 p-6 text-center sm:p-8">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-teal-700">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 16V8"/>
                                        <path d="m8 12 4-4 4 4"/>
                                        <path d="M20 16.5a4.5 4.5 0 0 0-1.8-8.6A6.5 6.5 0 0 0 5.5 9.5 3.5 3.5 0 0 0 6 16.5h2"/>
                                    </svg>
                                </span>

                                <label for="identity_file" class="mt-3 block cursor-pointer font-bold text-slate-900">
                                    Click or Drag an ID / Passport Photo
                                </label>

                                <div class="mt-4 flex justify-center">
                                    <input
                                        id="identity_file"
                                        name="identity_file"
                                        type="file"
                                        accept="image/jpeg,image/png,application/pdf"
                                        class="block w-fit max-w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:font-semibold file:text-teal-800 hover:file:bg-blue-200"
                                    >
                                </div>

                                <p class="mt-3 text-xs leading-5 text-slate-600">
                                    JPG, PNG, or PDF, maximum 2 MB. The file is stored in private storage.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">Special Notes / Additional Requests</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                maxlength="500"
                                placeholder="Example: Baby seat, English-speaking driver request, or an additional Malang–Batu route."
                                class="w-full rounded-xl border border-slate-200 bg-blue-50 px-4 py-3 text-base outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:ring-2 focus:ring-teal-100"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="min-w-0 lg:col-span-5">
                <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7 lg:sticky lg:top-6 lg:p-8">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-teal-700">Cost Summary</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">Your Booking Details</h2>
                        </div>
                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-2 text-xs font-bold text-teal-800">{{ $vehicle->category_label }}</span>
                    </div>

                    <div class="rounded-2xl bg-blue-50 p-4 sm:p-5">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="break-words font-bold text-slate-900">{{ $vehicle->name }}</p>
                                <p class="mt-1 text-sm text-slate-600">
                                    <span id="summaryOption">{{ $selectedOption['label'] ?? 'Rental option' }}</span>
                                    · {{ $vehicle->seats }} seats
                                </p>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                    <span id="summaryDate">{{ $rentalDateLabel }}</span>
                                    · <span id="summaryDuration">{{ $duration }}</span> days
                                    · <span id="summaryPax">{{ $pax }}</span> pax
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs font-semibold text-slate-500">Total Rental</p>
                                <p id="summaryTotal" class="text-lg font-extrabold text-slate-900 sm:text-2xl" aria-live="polite">{{ $formatRupiah($totalPrice) }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-700">Daily Rate</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        <span id="summaryRate">{{ $formatRupiah($selectedRate) }}</span> /day
                                        × <span id="summaryDays">{{ $duration }}</span> days
                                    </p>
                                </div>
                                <p id="summarySubtotal" class="shrink-0 text-right text-base font-extrabold text-slate-900">{{ $formatRupiah($totalPrice) }}</p>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-teal-700">Down Payment (DP {{ $downPaymentRate }}%)</p>
                                    <p class="mt-1 text-xs text-slate-500">Paid according to payment instructions</p>
                                </div>
                                <p id="summaryDp" class="shrink-0 text-right text-base font-extrabold text-teal-700 sm:text-xl">{{ $formatRupiah($downPayment) }}</p>
                            </div>

                            <div class="border-t border-slate-100"></div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-700">Remaining Balance ({{ 100 - $downPaymentRate }}%)</p>
                                    <p class="mt-1 text-xs text-slate-500">Paid according to the rental terms</p>
                                </div>
                                <p id="summaryRemaining" class="shrink-0 text-right text-base font-extrabold text-slate-900 sm:text-xl">{{ $formatRupiah($remainingPayment) }}</p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                        A booking is not considered paid until the down payment is verified. Do not send payment to an account that has not been verified by D'Java Tour &amp; Travel.
                    </p>

                    <button
                        type="submit"
                        id="continueToPayment"
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-5 py-4 text-base font-bold text-white shadow-sm transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        Continue to Payment
                        <span aria-hidden="true">&rarr;</span>
                    </button>

                    <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs font-semibold text-slate-600">
                        <div>
                            <span class="mb-2 flex justify-center text-teal-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m13 2-3 8h7l-6 12 2-9H6l7-11Z"/>
                                </svg>
                            </span>
                            Fast Confirmation
                        </div>
                        <div>
                            <span class="mb-2 flex justify-center text-teal-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3 14.5 5l3.2-.1.8 3.1 2.5 2-1.2 3 1.2 3-2.5 2-.8 3.1-3.2-.1L12 23l-2.5-2-3.2.1-.8-3.1-2.5-2 1.2-3-1.2-3 2.5-2 .8-3.1 3.2.1L12 3Z"/>
                                    <path d="m8.5 12 2.2 2.2 4.8-4.8"/>
                                </svg>
                            </span>
                            Verified Unit
                        </div>
                        <div>
                            <span class="mb-2 flex justify-center text-teal-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/>
                                </svg>
                            </span>
                            Route Assistance
                        </div>
                    </div>
                </section>
            </div>
        </form>
    </main>

    @include('components.footer.footer')
</body>
</html>

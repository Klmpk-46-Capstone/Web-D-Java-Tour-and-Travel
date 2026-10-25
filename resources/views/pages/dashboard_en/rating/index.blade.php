<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Write a Trip Review | D'Java Tour &amp; Travel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 lg:px-8">
            <a
                href="{{ localized_route('home') }}"
                class="flex items-center gap-3"
                aria-label="D'Java Tour & Travel"
            >
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-xl font-bold text-teal-700">
                    ✦
                </span>

                <span>
                    <span class="block text-base font-bold text-slate-900">
                        D'Java Tour & Travel
                    </span>
                    <span class="block text-xs font-semibold uppercase tracking-wide text-teal-700">
                        Explore East Java
                    </span>
                </span>
            </a>

            <nav class="flex flex-wrap items-center gap-2 text-sm font-medium">
                <a href="{{ localized_route('home') }}" class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Home
                </a>

                <a href="{{ localized_route('armada') }}" class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Vehicles
                </a>

                <a href="{{ localized_route('packages.index') }}" class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Tour Packages
                </a>

                <a
                    href="{{ localized_route('customer.bookings.index') }}"
                    class="rounded-full bg-teal-700 px-4 py-2 font-semibold text-white"
                >
                    Transaction History
                </a>
            </nav>

            <div class="flex items-center gap-2">
                @include('components.language-switcher', ['compact' => true, 'inline' => true])

                <a
                    href="{{ localized_route('customer.bookings.index') }}"
                    class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                >
                    Profile
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8 lg:py-10">

        {{-- BREADCRUMB --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3 text-sm">
            <p class="text-slate-500">
                <a href="{{ localized_route('home') }}" class="hover:text-teal-700">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ localized_route('customer.bookings.index') }}" class="hover:text-teal-700">Transaction History</a>
                <span class="mx-2">›</span>
                <span class="font-semibold text-slate-900">Write a Trip Review</span>
            </p>

            <a
                href="{{ localized_route('customer.bookings.detail', ['bookingCode' => $booking->booking_code]) }}"
                class="font-medium text-slate-600 hover:text-teal-700"
            >
                ← Back to Booking Details
            </a>
        </div>

        {{-- PAGE INTRO --}}
        <section class="mb-8">
            <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                How Was Your Trip Experience?
            </h1>

            <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
                Help other travelers by sharing an honest review of your trip
                and the comfort of D'Java Tour & Travel services.
            </p>
        </section>

        @if ($errors->any())
            <div
                id="review-error"
                class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                role="alert"
                aria-live="polite"
            >
                <span class="text-lg" aria-hidden="true">ⓘ</span>

                <div>
                    <p class="font-bold">Your review is incomplete</p>
                    <p class="mt-1 leading-6">
                        Please check your ratings and complete the remaining fields.
                    </p>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid items-start gap-8 xl:grid-cols-3">

            {{-- FORM RATING --}}
            <div class="space-y-6 xl:col-span-2">

                <form
                    id="review-form"
                    action="{{ localized_route('customer.bookings.rating', ['bookingCode' => $booking->booking_code]) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-6"
                >
                    @csrf

                    @include('pages.dashboard_en.rating.components.trip-summary')

                    <section class="rounded-2xl bg-white p-5 shadow-sm sm:p-7">
                        @include('pages.dashboard_en.rating.components.overall-rating')

                        <div class="mt-8">
                            @include('pages.dashboard_en.rating.components.service-rating')
                        </div>

                        <div class="mt-8">
                            @include('pages.dashboard_en.rating.components.experience-tags')
                        </div>

                        <div class="mt-8">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label for="review-comment" class="text-lg font-bold text-slate-900">
                                    Write About Your Full Experience
                                </label>

                                <span id="comment-count" class="text-sm text-slate-500">
                                    0 / 1000 characters
                                </span>
                            </div>

                            <textarea
                                name="comment"
                                id="review-comment"
                                rows="6"
                                maxlength="1000"
                                required
                                minlength="5"
                                placeholder="Tell us about your trip experience..."
                                class="mt-3 block w-full resize-y rounded-xl border border-slate-200 bg-white p-4 text-base leading-7 text-slate-800 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100"
                            >{{ old('comment') }}</textarea>

                            @error('comment')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-8">
                            @include('pages.dashboard_en.rating.components.photo-upload')
                        </div>

                        <div class="mt-8 space-y-3 rounded-xl bg-blue-50 p-4">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="is_public"
                                    value="1"
                                    checked
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                >

                                <span>
                                    <span class="block font-semibold text-slate-900">
                                        Show this review publicly
                                    </span>
                                    <span class="mt-1 block text-sm text-slate-600">
                                        Reviews may be displayed after moderation.
                                    </span>
                                </span>
                            </label>

                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-700">✓</span>

                                <span>
                                    <span class="block font-semibold text-slate-900">
                                        Verified Identity
                                    </span>
                                    <span class="mt-1 block text-sm text-slate-600">
                                        The review is linked to a paid account and booking.
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap justify-end gap-3">
                            <a
                                href="{{ localized_route('customer.bookings.detail', ['bookingCode' => $booking->booking_code]) }}"
                                class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                            >
                                Submit Review &amp; Rating →
                            </button>
                        </div>
                    </section>
                </form>
            </div>

            {{-- SIDEBAR --}}
            <aside class="space-y-6">
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">
                        ◆ Trip Information
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Your review helps future customers understand service quality based on
                        the experience of customers who actually made a booking.
                    </p>

                    <div class="mt-4 rounded-xl bg-blue-50 p-4">
                        <p class="text-sm font-semibold text-slate-900">
                            Verified booking
                        </p>

                        <p class="mt-1 break-all text-sm text-slate-600">
                            {{ $booking->booking_code }}
                        </p>
                    </div>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">
                        ♢ Community & Honesty Standards
                    </h2>

                    <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <li>✓ Rate based on your real experience.</li>
                        <li>✓ Avoid personal data and sensitive information in your comments.</li>
                        <li>✓ Use photos that are relevant to the trip.</li>
                        <li>✓ Reviews may be checked before being displayed publicly.</li>
                    </ul>
                </section>

                <section class="rounded-2xl bg-blue-100 p-5">
                    <p class="font-bold text-slate-900">
                        Have direct feedback?
                    </p>

                    <p class="mt-1 text-sm text-slate-600">
                        Contact customer service if you need assistance.
                    </p>

                    <a
                        href="{{ localized_route('customer.bookings.index') }}"
                        class="mt-4 inline-flex rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700"
                    >
                        Contact CS
                    </a>
                </section>
            </aside>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="mt-12 border-t border-slate-200 bg-blue-50">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:grid-cols-2 lg:grid-cols-3 lg:px-8">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">
                    D'Java Tour & Travel
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Travel services and vehicle rental for exploring East Java destinations.
                </p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900">Popular Destinations</h3>

                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Gunung Bromo</li>
                    <li>Kawah Ijen</li>
                    <li>Tumpak Sewu</li>
                    <li>Kota Batu</li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-slate-900">Contact & Service</h3>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Contact our customer service team through the official contact channels available on the website.
                </p>
            </div>
        </div>

        <div class="border-t border-slate-200">
            <div class="mx-auto flex max-w-7xl flex-wrap justify-between gap-3 px-5 py-4 text-xs text-slate-500 lg:px-8">
                <span>© {{ date('Y') }} D'Java Tour & Travel.</span>
                <span>Terms & Conditions · Privacy Policy</span>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('review-form');
            const overallValue = document.getElementById('overall-rating-value');
            const overallLabel = document.getElementById('overall-rating-label');
            const overallStars = document.querySelectorAll('[data-overall-star]');
            const comment = document.getElementById('review-comment');
            const commentCount = document.getElementById('comment-count');
            const photoInput = document.getElementById('review-photos');
            const photoPreview = document.getElementById('photo-preview');
            const photoCount = document.getElementById('photo-count');
            const photoError = document.getElementById('photo-error');

            const ratingLabels = {
                1: '1.0 • Very Disappointing',
                2: '2.0 • Less Satisfying',
                3: '3.0 • Satisfactory',
                4: '4.0 • Very Satisfying',
                5: '5.0 • Excellent & Beyond Expectations'
            };

            function paintStars(buttons, value) {
                buttons.forEach(function (button) {
                    const selected = Number(button.dataset.value ?? button.dataset.overallStar) <= value;

                    button.classList.toggle('text-orange-400', selected);
                    button.classList.toggle('text-slate-300', !selected);
                    button.setAttribute('aria-pressed', String(
                        Number(button.dataset.value ?? button.dataset.overallStar) === value
                    ));
                });
            }

            overallStars.forEach(function (button) {
                button.addEventListener('click', function () {
                    const value = Number(button.dataset.overallStar);

                    overallValue.value = value;
                    overallLabel.textContent = ratingLabels[value];
                    paintStars(overallStars, value);
                });
            });

            document.querySelectorAll('[data-service]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const service = button.dataset.service;
                    const value = Number(button.dataset.value);
                    const buttons = document.querySelectorAll('[data-service="' + service + '"]');

                    document.getElementById('service-value-' + service).value = value;
                    document.getElementById('service-label-' + service).textContent = value + '.0';

                    paintStars(buttons, value);
                });
            });

            function updateCommentCount() {
                commentCount.textContent = comment.value.length + ' / 1000 characters';
            }

            comment.addEventListener('input', updateCommentCount);
            updateCommentCount();

            photoInput.addEventListener('change', function () {
                photoPreview.replaceChildren();
                photoError.classList.add('hidden');

                const files = Array.from(photoInput.files || []);
                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                const maxSize = 5 * 1024 * 1024;

                if (files.length > 5) {
                    photoError.textContent = 'Maximum 5 photos.';
                    photoError.classList.remove('hidden');
                    photoInput.value = '';
                    photoCount.textContent = '0 of 5 photos';
                    return;
                }

                const invalidFile = files.find(function (file) {
                    return !validTypes.includes(file.type) || file.size > maxSize;
                });

                if (invalidFile) {
                    photoError.textContent = 'Use JPG, PNG, or WebP with a maximum size of 5 MB per photo.';
                    photoError.classList.remove('hidden');
                    photoInput.value = '';
                    photoCount.textContent = '0 of 5 photos';
                    return;
                }

                photoCount.textContent = files.length + ' of 5 photos';

                files.forEach(function (file) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'overflow-hidden rounded-xl bg-slate-100';

                    const image = document.createElement('img');
                    image.className = 'h-40 w-full object-cover';
                    image.alt = 'Trip photo preview';
                    image.src = URL.createObjectURL(file);

                    image.addEventListener('load', function () {
                        URL.revokeObjectURL(image.src);
                    });

                    wrapper.appendChild(image);
                    photoPreview.appendChild(wrapper);
                });
            });

            form.addEventListener('submit', function (event) {
                if (!overallValue.value) {
                    event.preventDefault();
                    overallLabel.textContent = 'Please select a rating first.';
                    overallLabel.classList.add('bg-red-100', 'text-red-700');
                    overallLabel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tulis Review Perjalanan | D'Java Tour &amp; Travel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 lg:px-8">
            <a
                href="{{ route('home') }}"
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
                <a href="{{ route('home') }}" class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Beranda
                </a>

                <a href="{{ url('/armada') }}" class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Armada
                </a>

                <a href="{{ url('/paket-wisata') }}" class="rounded-full px-4 py-2 hover:bg-slate-100">
                    Paket Wisata
                </a>

                <a
                    href="{{ route('customer.bookings.index') }}"
                    class="rounded-full bg-teal-700 px-4 py-2 font-semibold text-white"
                >
                    Riwayat Transaksi
                </a>
            </nav>

            <div class="flex items-center gap-2">
                <span class="rounded-full bg-teal-50 px-3 py-2 text-xs font-bold text-teal-700">ID</span>

                <a
                    href="{{ route('customer.bookings.index') }}"
                    class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                >
                    Profil
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8 lg:py-10">

        {{-- BREADCRUMB --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3 text-sm">
            <p class="text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-teal-700">Beranda</a>
                <span class="mx-2">›</span>
                <a href="{{ route('customer.bookings.index') }}" class="hover:text-teal-700">Riwayat Transaksi</a>
                <span class="mx-2">›</span>
                <span class="font-semibold text-slate-900">Tulis Review Trip</span>
            </p>

            <a
                href="{{ route('customer.bookings.detail', ['bookingCode' => $booking->booking_code]) }}"
                class="font-medium text-slate-600 hover:text-teal-700"
            >
                ← Kembali ke Detail Booking
            </a>
        </div>

        {{-- PAGE INTRO --}}
        <section class="mb-8">
            <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                Bagaimana Pengalaman Trip Anda?
            </h1>

            <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600 sm:text-lg">
                Bantu wisatawan lain dengan memberikan ulasan jujur untuk perjalanan Anda
                serta kenyamanan layanan D'Java Tour & Travel.
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
                    <p class="font-bold">Ulasan belum lengkap</p>
                    <p class="mt-1 leading-6">
                        Silakan periksa kembali penilaian dan lengkapi bagian yang masih kosong.
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
                    action="{{ route('customer.bookings.rating', ['bookingCode' => $booking->booking_code]) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-6"
                >
                    @csrf

                    @include('pages.dashboard.rating.components.trip-summary')

                    <section class="rounded-2xl bg-white p-5 shadow-sm sm:p-7">
                        @include('pages.dashboard.rating.components.overall-rating')

                        <div class="mt-8">
                            @include('pages.dashboard.rating.components.service-rating')
                        </div>

                        <div class="mt-8">
                            @include('pages.dashboard.rating.components.experience-tags')
                        </div>

                        <div class="mt-8">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label for="review-comment" class="text-lg font-bold text-slate-900">
                                    Tuliskan Pengalaman Lengkap Anda
                                </label>

                                <span id="comment-count" class="text-sm text-slate-500">
                                    0 / 1000 karakter
                                </span>
                            </div>

                            <textarea
                                name="comment"
                                id="review-comment"
                                rows="6"
                                maxlength="1000"
                                required
                                minlength="5"
                                placeholder="Ceritakan pengalaman Anda selama perjalanan..."
                                class="mt-3 block w-full resize-y rounded-xl border border-slate-200 bg-white p-4 text-base leading-7 text-slate-800 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-100"
                            >{{ old('comment') }}</textarea>

                            @error('comment')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-8">
                            @include('pages.dashboard.rating.components.photo-upload')
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
                                        Tampilkan ulasan ini secara publik
                                    </span>
                                    <span class="mt-1 block text-sm text-slate-600">
                                        Ulasan dapat ditampilkan setelah melewati proses moderasi.
                                    </span>
                                </span>
                            </label>

                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-700">✓</span>

                                <span>
                                    <span class="block font-semibold text-slate-900">
                                        Identitas Terverifikasi
                                    </span>
                                    <span class="mt-1 block text-sm text-slate-600">
                                        Ulasan terhubung dengan akun dan booking yang telah lunas.
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap justify-end gap-3">
                            <a
                                href="{{ route('customer.bookings.detail', ['bookingCode' => $booking->booking_code]) }}"
                                class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                            >
                                Kirim Ulasan & Rating →
                            </button>
                        </div>
                    </section>
                </form>
            </div>

            {{-- SIDEBAR --}}
            <aside class="space-y-6">
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">
                        ◆ Informasi Perjalanan
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Ulasanmu membantu calon pelanggan memahami kualitas layanan berdasarkan
                        pengalaman pelanggan yang benar-benar melakukan pemesanan.
                    </p>

                    <div class="mt-4 rounded-xl bg-blue-50 p-4">
                        <p class="text-sm font-semibold text-slate-900">
                            Booking terverifikasi
                        </p>

                        <p class="mt-1 break-all text-sm text-slate-600">
                            {{ $booking->booking_code }}
                        </p>
                    </div>
                </section>

                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">
                        ♢ Standar Komunitas & Kejujuran
                    </h2>

                    <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <li>✓ Berikan penilaian berdasarkan pengalaman nyata.</li>
                        <li>✓ Hindari data pribadi dan informasi sensitif dalam komentar.</li>
                        <li>✓ Gunakan foto yang relevan dengan perjalanan.</li>
                        <li>✓ Ulasan dapat diperiksa sebelum ditampilkan secara publik.</li>
                    </ul>
                </section>

                <section class="rounded-2xl bg-blue-100 p-5">
                    <p class="font-bold text-slate-900">
                        Ada kritik langsung?
                    </p>

                    <p class="mt-1 text-sm text-slate-600">
                        Hubungi layanan pelanggan jika membutuhkan bantuan.
                    </p>

                    <a
                        href="{{ route('customer.bookings.index') }}"
                        class="mt-4 inline-flex rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700"
                    >
                        Hubungi CS
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
                    Layanan perjalanan dan sewa armada untuk menjelajahi destinasi wisata Jawa Timur.
                </p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900">Destinasi Populer</h3>

                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Gunung Bromo</li>
                    <li>Kawah Ijen</li>
                    <li>Tumpak Sewu</li>
                    <li>Kota Batu</li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-slate-900">Kontak & Layanan</h3>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Hubungi tim layanan pelanggan melalui kanal kontak resmi yang tersedia di website.
                </p>
            </div>
        </div>

        <div class="border-t border-slate-200">
            <div class="mx-auto flex max-w-7xl flex-wrap justify-between gap-3 px-5 py-4 text-xs text-slate-500 lg:px-8">
                <span>© {{ date('Y') }} D'Java Tour & Travel.</span>
                <span>Syarat & Ketentuan · Kebijakan Privasi</span>
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
                1: '1.0 • Sangat Mengecewakan',
                2: '2.0 • Kurang Memuaskan',
                3: '3.0 • Cukup Memuaskan',
                4: '4.0 • Sangat Memuaskan',
                5: '5.0 • Sangat Memuaskan & Melebihi Ekspektasi'
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
                commentCount.textContent = comment.value.length + ' / 1000 karakter';
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
                    photoError.textContent = 'Maksimal 5 foto.';
                    photoError.classList.remove('hidden');
                    photoInput.value = '';
                    photoCount.textContent = '0 dari 5 foto';
                    return;
                }

                const invalidFile = files.find(function (file) {
                    return !validTypes.includes(file.type) || file.size > maxSize;
                });

                if (invalidFile) {
                    photoError.textContent = 'Gunakan JPG, PNG, atau WebP dengan ukuran maksimal 5 MB per foto.';
                    photoError.classList.remove('hidden');
                    photoInput.value = '';
                    photoCount.textContent = '0 dari 5 foto';
                    return;
                }

                photoCount.textContent = files.length + ' dari 5 foto';

                files.forEach(function (file) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'overflow-hidden rounded-xl bg-slate-100';

                    const image = document.createElement('img');
                    image.className = 'h-40 w-full object-cover';
                    image.alt = 'Pratinjau foto perjalanan';
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
                    overallLabel.textContent = 'Silakan pilih rating terlebih dahulu.';
                    overallLabel.classList.add('bg-red-100', 'text-red-700');
                    overallLabel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>D'Java Tour &amp; Travel | Explore East Java</title>
    <meta name="description" content="Discover East Java destinations and vehicle rental options with D'Java Tour &amp; Travel.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; scroll-padding-top: 100px; }
        body { font-family: 'DM Sans', Arial, sans-serif; }
        button, a { -webkit-tap-highlight-color: transparent; }
        img { display: block; max-width: 100%; }
    </style>
</head>
<body class="m-0 bg-indigo-50 text-base leading-relaxed text-slate-900 antialiased">
    @php
        $whatsappNumber = preg_replace('/\D+/', '', (string) config('services.djava.whatsapp', ''));
        $whatsappMessage = rawurlencode("Hello D'Java Tour And Travel, I would like to ask about your travel services.");
        $whatsappUrl = $whatsappNumber !== '' ? "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}" : null;
        $formatRupiah = static function ($amount) {
            return 'Rp ' . number_format((float) $amount, 0, '.', ',');
        };
    @endphp

    @include('components.navbar.navbar')

    <main>
        @include('pages.dashboard_en.beranda.components.form_pencarian')

        @if ($searchActive)
            @include('pages.dashboard_en.beranda.components.hasil_pencarian')

            @if ($mode === 'paket')
                @include('pages.dashboard_en.beranda.components.destinasi')
            @else
                @include('pages.dashboard_en.beranda.components.armada')
            @endif
        @else
            @include('pages.dashboard_en.beranda.components.rekomendasi_utama')
            @include('pages.dashboard_en.beranda.components.destinasi')
            @include('pages.dashboard_en.beranda.components.armada')
        @endif

        {{-- KEUNGGULAN LAYANAN --}}
        <section class="py-6 pb-10 md:py-8 md:pb-12">
            <div class="container mx-auto px-4">
                <div class="mb-5 flex flex-col items-start gap-2 md:mb-7 md:flex-row md:items-end md:justify-between md:gap-7">
                    <div>
                        <span class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-teal-700">WHY CHOOSE US</span>
                        <h2 class="m-0 text-3xl leading-tight tracking-tight md:text-4xl">Service Highlights</h2>
                    </div>
                    <p class="mb-0 mt-0 max-w-md text-sm leading-relaxed text-slate-500 md:mb-0.5">
                        Our commitment to delivering the best, safest, and most comfortable
                        travel experience on every East Java route.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 py-12 md:grid-cols-3 md:gap-10 md:py-16">
                    <div>
                        <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-teal-50 text-teal-700">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3 class="mb-2 mt-0 text-lg tracking-tight">Experienced Drivers</h3>
                        <p class="m-0 text-sm leading-relaxed text-slate-500">Professional drivers with in-depth knowledge of the terrain and tourist routes across East Java.</p>
                    </div>
                    <div>
                        <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-indigo-100 text-indigo-700">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <h3 class="mb-2 mt-0 text-lg tracking-tight">Well-Maintained Vehicles</h3>
                        <p class="m-0 text-sm leading-relaxed text-slate-500">Every vehicle undergoes regular inspections and maintenance for optimal safety and comfort.</p>
                    </div>
                    <div>
                        <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-blue-100 text-blue-700">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <h3 class="mb-2 mt-0 text-lg tracking-tight">24-Hour Service</h3>
                        <p class="m-0 text-sm leading-relaxed text-slate-500">Our customer support team is ready to help with travel consultations and reservations anytime.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- KONSULTASI / CTA --}}
        <section class="pb-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-start gap-6 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-white to-teal-50 p-6 shadow-md md:flex-row md:items-center md:justify-between md:gap-9 md:p-10">
                    <div class="max-w-3xl">
                        <span class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-teal-700">NEED SOMETHING SPECIAL?</span>
                        <h2 class="mb-3 mt-0 text-3xl leading-tight tracking-tight">Plan a Custom Trip With Us</h2>
                        <p class="m-0 max-w-2xl text-sm leading-relaxed text-slate-500">Have your own itinerary or need recommendations for a group? Discuss your travel plan directly with our expert team via WhatsApp.</p>
                    </div>
                    <div class="flex w-full flex-col flex-wrap items-stretch justify-end gap-3 md:w-auto md:flex-row">
                        @if ($whatsappUrl)
                            <a class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-center text-sm font-bold text-white no-underline transition hover:bg-teal-800" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Consult via WhatsApp</a>
                        @else
                            <a class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-center text-sm font-bold text-white no-underline transition hover:bg-teal-800" href="#kontak">Contact Us</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('components.footer.footer')
</body>
</html>

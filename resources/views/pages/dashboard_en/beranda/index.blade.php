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
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', Arial, sans-serif; }
        button, a { -webkit-tap-highlight-color: transparent; }
        img { display: block; max-width: 100%; }
    </style>
</head>
<body class="m-0 bg-indigo-50 text-base leading-relaxed text-slate-900 antialiased scroll-pt-24">
    @php
        $rawWhatsapp = (string) config('services.djava.whatsapp', '');
        $whatsappNumber = preg_replace('/\D+/', '', $rawWhatsapp);
        $whatsappMessage = rawurlencode("Hello D'Java Tour And Travel, I would like to ask about your travel services.");
        $whatsappUrl = !empty($whatsappNumber) ? "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}" : null;

        $formatRupiah = static function ($amount) {
            return 'Rp ' . number_format((float) $amount, 0, '.', ',');
        };
    @endphp

    @include('components.navbar.navbar')

    <main>
        <div class="pt-6"><x-catalog.validation-errors /></div>
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

        {{-- WHY CHOOSE US --}}
        <section class="bg-indigo-50 py-12 md:py-16">
            <div class="container mx-auto px-4">

                {{-- Heading --}}
                <div class="mb-10 flex flex-col justify-between gap-4 md:mb-14 md:flex-row md:items-end">
                    <div>
                        <span class="mb-3 block text-xs font-bold uppercase tracking-widest text-teal-700">
                            Why Choose Us
                        </span>

                        <h2 class="m-0 text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                            Our Service Advantages
                        </h2>
                    </div>

                    <p class="m-0 max-w-md text-sm leading-relaxed text-slate-500 md:text-base">
                        We are committed to delivering an exceptional travel experience,
                        with safety and comfort on every journey across East Java.
                    </p>
                </div>

                {{-- Service Information --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3 md:gap-10">

                    {{-- Experienced Drivers --}}
                    <article class="border-t border-slate-300 pt-5">
                        <div class="mb-8 flex items-center justify-between">
                            <span class="text-xs font-bold tracking-widest text-teal-700">
                                01 / DRIVERS
                            </span>

                            <span class="text-xs text-slate-400">
                                D'JAVA SERVICE
                            </span>
                        </div>

                        <div class="mb-4 h-1 w-10 bg-teal-700"></div>

                        <h3 class="mb-3 text-xl font-semibold tracking-tight text-slate-900">
                            Experienced Drivers
                        </h3>

                        <p class="m-0 max-w-sm text-sm leading-relaxed text-slate-500">
                            Professional drivers with in-depth knowledge of local roads
                            and tourist routes throughout East Java.
                        </p>
                    </article>

                    {{-- Well-Maintained Fleet --}}
                    <article class="border-t border-slate-300 pt-5">
                        <div class="mb-8 flex items-center justify-between">
                            <span class="text-xs font-bold tracking-widest text-teal-700">
                                02 / FLEET
                            </span>

                            <span class="text-xs text-slate-400">
                                D'JAVA SERVICE
                            </span>
                        </div>

                        <div class="mb-4 h-1 w-10 bg-teal-700"></div>

                        <h3 class="mb-3 text-xl font-semibold tracking-tight text-slate-900">
                            Well-Maintained Fleet
                        </h3>

                        <p class="m-0 max-w-sm text-sm leading-relaxed text-slate-500">
                            Every vehicle undergoes regular inspections and scheduled
                            maintenance to ensure safety and comfort throughout your journey.
                        </p>
                    </article>

                    {{-- 24/7 Customer Support --}}
                    <article class="border-t border-slate-300 pt-5">
                        <div class="mb-8 flex items-center justify-between">
                            <span class="text-xs font-bold tracking-widest text-teal-700">
                                03 / SUPPORT
                            </span>

                            <span class="text-xs text-slate-400">
                                D'JAVA SERVICE
                            </span>
                        </div>

                        <div class="mb-4 h-1 w-10 bg-teal-700"></div>

                        <h3 class="mb-3 text-xl font-semibold tracking-tight text-slate-900">
                            24/7 Customer Support
                        </h3>

                        <p class="m-0 max-w-sm text-sm leading-relaxed text-slate-500">
                            Our support team is available around the clock to assist
                            you with travel inquiries and booking arrangements.
                        </p>
                    </article>

                </div>

            </div>
        </section>

        {{-- KONSULTASI / CTA --}}
        <section class="pb-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-start gap-6 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-white to-teal-50 p-6 shadow-md md:flex-row md:items-center md:justify-between md:gap-9 md:p-10">
                    <div class="max-w-3xl">
                        <span class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-teal-700">NEED SOMETHING SPECIAL?</span>
                        <h2 class="mb-3 mt-0 text-3xl leading-tight tracking-tight text-slate-900">Plan a Custom Trip With Us</h2>
                        <p class="m-0 max-w-2xl text-sm leading-relaxed text-slate-500">Have your own itinerary or need recommendations for a group? Discuss your travel plan directly with our expert team via WhatsApp.</p>
                    </div>
                    <div class="flex w-full flex-col flex-wrap items-stretch justify-end gap-3 md:w-auto md:flex-row">
                        @if ($whatsappUrl)
                            <a class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-center text-sm font-bold text-white no-underline transition hover:bg-teal-800" href="{{ e($whatsappUrl) }}" target="_blank" rel="noopener noreferrer">Consult via WhatsApp</a>
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

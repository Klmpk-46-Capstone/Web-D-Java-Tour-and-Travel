<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>D'Java Tour &amp; Travel | Eksplorasi Jawa Timur</title>
    <meta name="description" content="Temukan destinasi dan pilihan armada perjalanan Jawa Timur dari D'Java Tour &amp; Travel.">
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
        $whatsappMessage = rawurlencode("Halo D'Java Tour And Travel, saya ingin bertanya mengenai layanan perjalanan.");
        $whatsappUrl = !empty($whatsappNumber) ? "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}" : null;

        $formatRupiah = static function ($amount) {
            return 'Rp ' . number_format((float) $amount, 0, ',', '.');
        };
    @endphp

    @include('components.navbar.navbar')

    <main>
        <div class="pt-6"><x-catalog.validation-errors /></div>
        @include('pages.dashboard.beranda.components.form_pencarian')

        @if ($searchActive)
            @include('pages.dashboard.beranda.components.hasil_pencarian')

            @if ($mode === 'paket')
                @include('pages.dashboard.beranda.components.destinasi')
            @else
                @include('pages.dashboard.beranda.components.armada')
            @endif
        @else
            @include('pages.dashboard.beranda.components.rekomendasi_utama')
            @include('pages.dashboard.beranda.components.destinasi')
            @include('pages.dashboard.beranda.components.armada')
        @endif

        {{-- KEUNGGULAN LAYANAN --}}
        <section class="bg-indigo-50 py-12 md:py-16">
            <div class="container mx-auto px-4">

                {{-- Heading --}}
                <div class="mb-10 flex flex-col justify-between gap-4 md:mb-14 md:flex-row md:items-end">
                    <div>
                        <span class="mb-3 block text-xs font-bold uppercase tracking-widest text-teal-700">
                            Kenapa Memilih Kami
                        </span>

                        <h2 class="m-0 text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                            Keunggulan Layanan
                        </h2>
                    </div>

                    <p class="m-0 max-w-md text-sm leading-relaxed text-slate-500 md:text-base">
                        Komitmen kami untuk memberikan pengalaman perjalanan terbaik,
                        aman, dan nyaman di setiap rute Jawa Timur.
                    </p>
                </div>

                {{-- Service Information --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3 md:gap-10">

                    {{-- Driver --}}
                    <article class="border-t border-slate-300 pt-5">
                        <div class="mb-8 flex items-center justify-between">
                            <span class="text-xs font-bold tracking-widest text-teal-700">
                                01 / DRIVER
                            </span>

                            <span class="text-xs text-slate-400">
                                D'JAVA SERVICE
                            </span>
                        </div>

                        <div class="mb-4 h-1 w-10 bg-teal-700"></div>

                        <h3 class="mb-3 text-xl font-semibold tracking-tight text-slate-900">
                            Driver Berpengalaman
                        </h3>

                        <p class="m-0 max-w-sm text-sm leading-relaxed text-slate-500">
                            Driver profesional yang menguasai medan dan rute wisata
                            di seluruh wilayah Jawa Timur secara mendalam.
                        </p>
                    </article>

                    {{-- Armada --}}
                    <article class="border-t border-slate-300 pt-5">
                        <div class="mb-8 flex items-center justify-between">
                            <span class="text-xs font-bold tracking-widest text-teal-700">
                                02 / ARMADA
                            </span>

                            <span class="text-xs text-slate-400">
                                D'JAVA SERVICE
                            </span>
                        </div>

                        <div class="mb-4 h-1 w-10 bg-teal-700"></div>

                        <h3 class="mb-3 text-xl font-semibold tracking-tight text-slate-900">
                            Armada Terawat
                        </h3>

                        <p class="m-0 max-w-sm text-sm leading-relaxed text-slate-500">
                            Kendaraan selalu melalui pengecekan rutin dan perawatan
                            berkala demi keselamatan dan kenyamanan optimal.
                        </p>
                    </article>

                    {{-- Customer Service --}}
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
                            Layanan 24 Jam
                        </h3>

                        <p class="m-0 max-w-sm text-sm leading-relaxed text-slate-500">
                            Tim dukungan pelanggan yang siap membantu kebutuhan
                            konsultasi dan reservasi perjalanan Anda kapan saja.
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
                        <span class="mb-2 block text-xs font-extrabold uppercase tracking-wider text-teal-700">BUTUH BANTUAN KHUSUS?</span>
                        <h2 class="mb-3 mt-0 text-3xl leading-tight tracking-tight text-slate-900">Rencanakan Perjalanan Custom Bersama Kami</h2>
                        <p class="m-0 max-w-2xl text-sm leading-relaxed text-slate-500">Punya itinerary sendiri atau butuh rekomendasi khusus untuk rombongan? Konsultasikan rencana perjalanan Anda langsung dengan tim ahli kami melalui WhatsApp.</p>
                    </div>
                    <div class="flex w-full flex-col flex-wrap items-stretch justify-end gap-3 md:w-auto md:flex-row">
                        @if ($whatsappUrl)
                            <a class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-center text-sm font-bold text-white no-underline transition hover:bg-teal-800" href="{{ e($whatsappUrl) }}" target="_blank" rel="noopener noreferrer">Konsultasi via WhatsApp</a>
                        @else
                            <a class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-center text-sm font-bold text-white no-underline transition hover:bg-teal-800" href="#kontak">Hubungi Kami</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('components.footer.footer')
</body>
</html>

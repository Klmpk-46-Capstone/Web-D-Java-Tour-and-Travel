<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Katalog Armada - D'Java Tour &amp; Travel</title>
    <meta name="description" content="Pilihan armada D'Java Tour & Travel untuk perjalanan wisata Jawa Timur: MPV keluarga, premium hybrid, dan minibus rombongan.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'DM Sans', Arial, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-indigo-50/50 text-slate-800 antialiased">

    @php
        $whatsappNumber = preg_replace('/\D+/', '', (string) config('services.djava.whatsapp', ''));
        $busText = rawurlencode("Halo D'Java Tour & Travel, saya ingin konsultasi sewa medium bus / big bus pariwisata.");
        $busWhatsappUrl = $whatsappNumber !== ''
            ? "https://wa.me/{$whatsappNumber}?text={$busText}"
            : route('home') . '#kontak';

        $sortOptions = [
            'rekomendasi' => 'Rekomendasi',
            'harga_terendah' => 'Harga Terendah',
            'harga_tertinggi' => 'Harga Tertinggi',
            'kapasitas' => 'Kapasitas Terbesar',
        ];

        $hasActiveFilter = filled($q) || filled($category) || filled($transmission);
        $filteredQuery = request()->except(['kategori', 'page']);
    @endphp

    @include('components.navbar.navbar')

    <main class="flex-1 py-8 md:py-12">
        <x-ui.page-container>

            {{-- HEADER KATALOG --}}
            <div class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <span class="mb-1 block text-xs font-extrabold uppercase tracking-wider text-teal-700">
                        GARASI &amp; LAYANAN TRANSPORTASI
                    </span>

                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                        Pilihan Armada
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Kendaraan terawat dengan standar servis berkala, driver ramah,
                        dan proteksi perjalanan lengkap untuk rute Jawa Timur.
                    </p>
                </div>

                {{-- FILTER KATEGORI CEPAT --}}
                <div class="flex w-full items-center gap-1 overflow-x-auto rounded-full border border-slate-200 bg-white p-1.5 shadow-sm lg:w-auto">
                    <a
                        href="{{ route('armada', $filteredQuery) }}"
                        class="{{ filled($category) ? 'text-slate-600 hover:bg-slate-50' : 'bg-teal-700 text-white' }} whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold no-underline transition"
                    >
                        Semua Unit
                    </a>

                    @foreach ($categories as $categoryKey => $categoryLabel)
                        <a
                            href="{{ route('armada', array_merge($filteredQuery, ['kategori' => $categoryKey])) }}"
                            class="{{ $category === $categoryKey ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-slate-50' }} whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold no-underline transition"
                        >
                            {{ $categoryLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- FILTER DAN PENCARIAN --}}
            <x-catalog.filter-card :action="route('armada')">
                <x-catalog.text-field
                    name="q"
                    label="Cari armada"
                    :value="$q"
                    placeholder="Cari nama unit, tipe, atau transmisi..."
                    span="xl:col-span-5"
                />

                <x-catalog.select-field
                    name="transmisi"
                    label="Transmisi"
                    :options="[
                        'at' => 'Otomatis / AT',
                        'mt' => 'Manual / MT',
                    ]"
                    :selected="$transmission"
                    placeholder="Semua Transmisi"
                    span="xl:col-span-4"
                />

                <div class="flex items-center gap-2 md:col-span-2 xl:col-span-3">
                    <x-ui.button type="submit" size="lg" class="flex-1">
                        Cari Armada
                    </x-ui.button>

                    @if ($hasActiveFilter)
                        <x-ui.button
                            href="{{ route('armada') }}"
                            variant="ghost-danger"
                            size="sm"
                            class="whitespace-nowrap"
                        >
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </x-catalog.filter-card>

            {{-- RINGKASAN HASIL & URUTAN --}}
            <x-catalog.result-toolbar
                :action="route('armada')"
                :sort="$sort"
                :options="$sortOptions"
            >
                Menampilkan
                <strong class="font-bold text-slate-800">{{ $vehicles->total() }}</strong>
                unit armada
                @if (filled($q))
                    untuk pencarian &ldquo;{{ $q }}&rdquo;
                @endif
            </x-catalog.result-toolbar>

            {{-- DAFTAR ARMADA --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($vehicles as $vehicle)
                    <x-ui.card as="article" padding="none" interactive class="group flex min-w-0 flex-col overflow-hidden">

                        <div class="relative h-56 overflow-hidden bg-slate-100">
                            @if ($vehicle->image_url)
                                <img
                                    src="{{ $vehicle->image_url }}"
                                    alt="{{ $vehicle->name }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="grid h-full place-items-center px-6 text-center text-sm text-slate-500">
                                    Foto armada belum tersedia.
                                </div>
                            @endif

                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/10 to-transparent"></div>

                            <div class="absolute left-3 top-3">
                                <x-ui.badge variant="solid">
                                    {{ $vehicle->badge ?: 'ARMADA PILIHAN' }}
                                </x-ui.badge>
                            </div>

                            <div class="absolute bottom-3 left-3 right-3 text-white">
                                <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-teal-200">
                                    {{ $vehicle->category_label }}
                                </span>

                                <h2 class="text-xl font-extrabold leading-tight text-white">
                                    {{ $vehicle->name }}
                                </h2>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            <p class="line-clamp-2 text-xs leading-relaxed text-slate-500">
                                {{ $vehicle->description ?: 'Informasi unit dapat dikonfirmasi kepada tim D’Java Tour & Travel.' }}
                            </p>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <x-ui.stat-tile label="Kursi" :value="$vehicle->seats . ' kursi'" class="text-center" />
                                <x-ui.stat-tile label="Bagasi" :value="$vehicle->luggage_capacity . ' koper'" class="text-center" />
                                <x-ui.stat-tile label="Transmisi" :value="$vehicle->transmission ?: 'Hubungi kami'" class="text-center" />
                            </div>

                            <div class="mt-5 flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
                                <div class="min-w-0">
                                    <small class="mb-1 block text-xs font-bold uppercase text-slate-400">
                                        Mulai Dari
                                    </small>

                                    <span class="text-lg font-black text-teal-700">
                                        {{ $vehicle->formatted_lowest_price ?? $vehicle->formatted_price }}
                                    </span>

                                    <span class="text-xs text-slate-500">/hari</span>
                                </div>

                                <x-ui.button
                                    href="{{ route('armada.show', $vehicle->slug) }}"
                                    size="md"
                                    class="shrink-0"
                                >
                                    <span>Lihat Detail</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </x-ui.button>
                            </div>
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.empty-state
                        title="Armada Tidak Ditemukan"
                        description="Tidak ada armada yang cocok dengan filter atau pencarian Anda. Silakan coba kata kunci lain atau reset filter."
                        class="col-span-full"
                    >
                        <x-ui.button href="{{ route('armada') }}" size="md">
                            Reset Filter
                        </x-ui.button>
                    </x-ui.empty-state>
                @endforelse
            </div>

            {{-- PAGINATION --}}
            @if ($vehicles->hasPages())
                <div class="mt-8">
                    {{ $vehicles->links() }}
                </div>
            @endif

            {{-- CTA BUS PARIWISATA --}}
            <div class="mt-10 flex flex-col items-start justify-between gap-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:p-8">
                <div class="flex items-start gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-teal-50 text-teal-700">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="13" rx="2"/>
                            <path d="M3 11h18M7 17v3M17 17v3M7 8h.01M17 8h.01"/>
                        </svg>
                    </div>

                    <div class="max-w-2xl">
                        <h2 class="m-0 text-lg font-extrabold tracking-tight text-slate-900">
                            Butuh Medium Bus atau Big Bus Pariwisata?
                        </h2>

                        <p class="mt-1 text-sm leading-relaxed text-slate-500">
                            Tersedia armada Bus 31 - 50 kursi untuk gathering kantor,
                            study tour, dan ziarah se-Jawa Timur.
                        </p>
                    </div>
                </div>

                <x-ui.button
                    href="{{ $busWhatsappUrl }}"
                    variant="whatsapp"
                    size="lg"
                    class="w-full shrink-0 md:w-auto"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/>
                    </svg>
                    <span>Konsultasi Bus via WhatsApp</span>
                </x-ui.button>
            </div>

        </x-ui.page-container>
    </main>

    @include('components.footer.footer')

</body>
</html>

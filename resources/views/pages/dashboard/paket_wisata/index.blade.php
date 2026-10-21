<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Katalog Paket Wisata - D'Java Tour &amp; Travel</title>

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

    @include('components.navbar.navbar')

    <main class="flex-1 py-8 md:py-12">
        <div class="container mx-auto px-4">

            {{-- HEADER KATALOG --}}
            <div class="mb-8 max-w-2xl">
                <span class="mb-1 block text-xs font-extrabold uppercase tracking-wider text-teal-700">
                    EKSPLORASI TANPA BATAS
                </span>

                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                    Katalog Paket Wisata &amp; Open Trip
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Pilihan paket liburan terlengkap di Karimunjawa, Gunung Bromo,
                    Kawah Ijen, Dieng Plateau, dan Yogyakarta Heritage.
                </p>
            </div>

            {{-- FILTER DAN PENCARIAN --}}
            <div class="mb-8 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:p-6">
                <form
                    action="{{ route('packages.index') }}"
                    method="GET"
                    class="grid grid-cols-1 gap-3 md:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <label for="package-search" class="sr-only">
                            Cari paket wisata
                        </label>

                        <input
                            id="package-search"
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari destinasi atau nama paket wisata..."
                            maxlength="150"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-teal-700 focus:bg-white focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="package-route" class="sr-only">
                            Pilih rute wisata
                        </label>

                        <select
                            id="package-route"
                            name="rute"
                            onchange="this.form.submit()"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-teal-700 focus:bg-white focus:outline-none"
                        >
                            <option value="">Semua Rute Wisata</option>
                            <option value="bahari" @selected(request('rute') === 'bahari')>
                                Wisata Bahari (Karimunjawa)
                            </option>
                            <option value="gunung" @selected(request('rute') === 'gunung')>
                                Mountain Trekking (Bromo/Dieng/Ijen)
                            </option>
                            <option value="heritage" @selected(request('rute') === 'heritage')>
                                Heritage Culture (Jogja)
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="flex-1 rounded-2xl bg-teal-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-teal-800"
                        >
                            Cari Paket
                        </button>

                        @if (request('q') || request('rute'))
                            <a
                                href="{{ route('packages.index') }}"
                                class="px-3 py-3 text-xs font-bold text-rose-600 hover:underline"
                            >
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- DAFTAR PAKET WISATA --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($packages as $pkg)
                    <article class="group flex min-w-0 flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-xl">

                        <div>
                            {{-- GAMBAR PAKET --}}
                            <div class="relative h-64 overflow-hidden bg-slate-100">
                                @php
                                    $imageUrl = $pkg->image_url
                                        ?: 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80';
                                @endphp

                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $pkg->name }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async"
                                >

                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                                <div class="absolute left-3 top-3">
                                    <span class="rounded-full bg-teal-600 px-3 py-1 text-xs font-extrabold text-white shadow-sm">
                                        {{ $pkg->badge ?: 'OPEN TRIP' }}
                                    </span>
                                </div>

                                <div class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-900 shadow-sm backdrop-blur-sm">
                                    <span class="text-amber-500" aria-hidden="true">★</span>
                                    <span>{{ number_format((float) $pkg->rating, 2) }}</span>
                                    <span class="font-normal text-slate-500">
                                        ({{ (int) $pkg->total_reviews }})
                                    </span>
                                </div>

                                <div class="absolute bottom-3 left-3 right-3 text-white">
                                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-teal-200">
                                        {{ $pkg->category }}
                                    </span>

                                    <h2 class="text-xl font-extrabold leading-tight text-white">
                                        {{ $pkg->name }}
                                    </h2>
                                </div>
                            </div>

                            {{-- DESKRIPSI DAN INFORMASI --}}
                            <div class="p-5">
                                <p class="mb-4 line-clamp-2 text-xs leading-relaxed text-slate-500">
                                    {{ $pkg->description }}
                                </p>

                                <div class="mb-4 space-y-2 rounded-2xl border border-slate-100 bg-slate-50 p-3 text-xs">
                                    <div class="flex items-center justify-between gap-3 text-slate-600">
                                        <span class="text-slate-400">Durasi:</span>
                                        <strong class="text-right text-slate-800">
                                            {{ $pkg->duration }}
                                        </strong>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 text-slate-600">
                                        <span class="text-slate-400">Itinerary Rute:</span>
                                        <span class="text-right font-bold text-teal-700">
                                            {{ $pkg->itineraries->count() }} Hari Kegiatan
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- HARGA DAN DETAIL --}}
                        <div class="mt-2 flex items-center justify-between gap-3 border-t border-slate-100 p-5 pt-4">
                            <div class="min-w-0">
                                <small class="mb-1 block text-xs font-bold uppercase text-slate-400">
                                    Mulai Dari
                                </small>

                                <span class="text-lg font-black text-teal-700">
                                    Rp {{ number_format((float) $pkg->price, 0, ',', '.') }}
                                </span>

                                <span class="text-xs text-slate-500">/pax</span>
                            </div>

                            <a
                                href="{{ route('packages.show', $pkg->slug) }}"
                                class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-teal-700 px-4 py-3 text-xs font-bold text-white no-underline shadow-sm transition hover:bg-teal-800"
                            >
                                <span>Lihat Detail</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                        <strong class="mb-1 block text-lg text-slate-800">
                            Paket Wisata Tidak Ditemukan
                        </strong>

                        Tidak ada paket wisata yang cocok dengan kriteria pencarian Anda.
                        Silakan coba kata kunci lain.
                    </div>
                @endforelse
            </div>

            {{-- PAGINATION --}}
            @if ($packages->hasPages())
                <div class="mt-8">
                    {{ $packages->links() }}
                </div>
            @endif

        </div>
    </main>

    @include('components.footer.footer')

</body>
</html>

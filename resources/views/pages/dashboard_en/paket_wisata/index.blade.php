<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tour Package Catalog - D'Java Tour &amp; Travel</title>

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
        <x-ui.page-container>

            {{-- HEADER KATALOG --}}
            <div class="mb-8 max-w-2xl">
                <span class="mb-1 block text-xs font-extrabold uppercase tracking-wider text-teal-700">
                    EXPLORE WITHOUT LIMITS
                </span>

                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                    Tour Package &amp; Open Trip Catalog
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    The most complete selection of holiday packages in Karimunjawa, Mount Bromo,
                    Ijen Crater, Dieng Plateau, and Yogyakarta Heritage.
                </p>
            </div>

            {{-- PENCARIAN DAN FILTER KATEGORI --}}
            @php
                $sortOptions = [
                    'rekomendasi' => 'Recommended',
                    'harga_terendah' => 'Lowest Price',
                    'harga_tertinggi' => 'Highest Price',
                    'rating' => 'Highest Rating',
                ];

                $hasActiveFilter = filled($q) || filled($category);
            @endphp

            <x-catalog.filter-card :action="localized_route('packages.index')">
                <x-catalog.text-field
                    name="q"
                    label="Search tour packages"
                    :value="$q"
                    placeholder="Search tour package names..."
                    span="xl:col-span-5"
                />

                <x-catalog.select-field
                    name="kategori"
                    label="Tour Category"
                    :options="$categories"
                    :selected="$category"
                    placeholder="All Tour Categories"
                    span="xl:col-span-4"
                    :autoSubmit="false"
                />

                <div class="flex items-center gap-2 md:col-span-2 xl:col-span-3">
                    <x-ui.button type="submit" size="lg" class="flex-1">
                        Search Packages
                    </x-ui.button>

                    @if ($hasActiveFilter)
                        <x-ui.button
                            href="{{ localized_route('packages.index') }}"
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
                :action="localized_route('packages.index')"
                :sort="$sort"
                :options="$sortOptions"
            >
                Showing
                <strong class="font-bold text-slate-800">{{ $packages->total() }}</strong>
                tour packages
                @if (filled($q))
                    for search &ldquo;{{ $q }}&rdquo;
                @endif
                @if (filled($category))
                    category <strong class="font-bold text-slate-800">{{ $categories[$category] ?? $category }}</strong>
                @endif
            </x-catalog.result-toolbar>

            {{-- DAFTAR PAKET WISATA --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($packages as $pkg)
                    <x-ui.card as="article" padding="none" interactive class="group flex min-w-0 flex-col justify-between overflow-hidden">

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
                                    <x-ui.badge variant="solid">
                                        {{ $pkg->badge ?: 'OPEN TRIP' }}
                                    </x-ui.badge>
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
                                        <span class="text-slate-400">Duration:</span>
                                        <strong class="text-right text-slate-800">
                                            {{ $pkg->duration }}
                                        </strong>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 text-slate-600">
                                        <span class="text-slate-400">Route Itinerary:</span>
                                        <span class="text-right font-bold text-teal-700">
                                            {{ $pkg->itineraries->count() }} Activity Days
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- HARGA DAN DETAIL --}}
                        <div class="mt-2 flex items-center justify-between gap-3 border-t border-slate-100 p-5 pt-4">
                            <div class="min-w-0">
                                <small class="mb-1 block text-xs font-bold uppercase text-slate-400">
                                    Starting From
                                </small>

                                <span class="text-lg font-black text-teal-700">
                                    Rp {{ number_format((float) $pkg->price, 0, '.', ',') }}
                                </span>

                                <span class="text-xs text-slate-500">/pax</span>
                            </div>

                            <x-ui.button
                                href="{{ localized_route('packages.show', $pkg->slug) }}"
                                size="md"
                                class="shrink-0"
                            >
                                <span>View Details</span>
                                <span aria-hidden="true">&rarr;</span>
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.empty-state
                        title="No Tour Packages Found"
                        description="No tour packages match your search criteria. Please try another keyword."
                        class="col-span-full"
                    >
                        <x-ui.button href="{{ localized_route('packages.index') }}" size="md">
                            Reset Filters
                        </x-ui.button>
                    </x-ui.empty-state>
                @endforelse
            </div>

            {{-- PAGINATION --}}
            @if ($packages->hasPages())
                <div class="mt-8">
                    {{ $packages->links() }}
                </div>
            @endif

        </x-ui.page-container>
    </main>

    @include('components.footer.footer')

</body>
</html>

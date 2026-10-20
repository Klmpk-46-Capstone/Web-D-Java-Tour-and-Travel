<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Katalog Paket Wisata Jawa Timur - D'Java Tour &amp; Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', Arial, sans-serif; }
    </style>
</head>
<body class="bg-indigo-50/50 text-slate-800 antialiased min-h-screen flex flex-col">
    @include('components.navbar.navbar')

    <main class="flex-1 py-8 md:py-12">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="max-w-2xl mb-8">
                <span class="text-xs font-extrabold uppercase tracking-wider text-teal-700 block mb-1">
                    EKSPLORASI TANPA BATAS
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Katalog Paket Wisata &amp; Open Trip
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    Pilihan paket liburan terlengkap di Karimunjawa, Gunung Bromo, Kawah Ijen, Dieng Plateau, dan Yogyakarta Heritage.
                </p>
            </div>

            {{-- Filter & Search Form --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-4 md:p-6 shadow-sm mb-8">
                <form action="{{ route('packages.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ request('q') }}" 
                            placeholder="Cari destinasi atau nama paket wisata..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:border-teal-700"
                        >
                    </div>

                    <div>
                        <select name="rute" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:border-teal-700">
                            <option value="">Semua Rute Wisata</option>
                            <option value="bahari" {{ request('rute') === 'bahari' ? 'selected' : '' }}>Wisata Bahari (Karimunjawa)</option>
                            <option value="gunung" {{ request('rute') === 'gunung' ? 'selected' : '' }}>Mountain Trekking (Bromo/Dieng/Ijen)</option>
                            <option value="heritage" {{ request('rute') === 'heritage' ? 'selected' : '' }}>Heritage Culture (Jogja)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 bg-teal-700 hover:bg-teal-800 text-white font-bold py-3 px-4 rounded-2xl text-sm transition">
                            Cari Paket
                        </button>
                        @if(request('q') || request('rute'))
                            <a href="{{ route('packages.index') }}" class="px-3 py-3 text-xs text-rose-600 font-bold hover:underline">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Packages Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($packages as $pkg)
                    <article class="group bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="relative h-64 overflow-hidden bg-slate-100">
                                <img 
                                    src="{{ $pkg->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80' }}" 
                                    alt="{{ $pkg->name }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-teal-600 text-white shadow-sm">
                                        {{ $pkg->badge ?? 'OPEN TRIP' }}
                                    </span>
                                </div>

                                <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-full text-xs font-bold text-slate-900 flex items-center gap-1 shadow-xs">
                                    <span class="text-amber-500">★</span>
                                    <span>{{ $pkg->rating }}</span>
                                    <span class="text-slate-400 font-normal">({{ $pkg->total_reviews }})</span>
                                </div>

                                <div class="absolute bottom-3 left-3 right-3 text-white">
                                    <span class="text-xs font-bold uppercase tracking-wider text-teal-200 block mb-0.5">
                                        {{ $pkg->category }}
                                    </span>
                                    <h2 class="text-xl font-extrabold text-white leading-tight">
                                        {{ $pkg->name }}
                                    </h2>
                                </div>
                            </div>

                            <div class="p-5">
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                                    {{ $pkg->description }}
                                </p>

                                <div class="space-y-1.5 mb-4 bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400">Durasi:</span>
                                        <strong class="text-slate-800">{{ $pkg->duration }}</strong>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400">Itinerary Rute:</span>
                                        <span class="font-bold text-teal-700">{{ $pkg->itineraries->count() }} Hari Kegiatan</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 pt-0 flex items-center justify-between border-t border-slate-100 mt-2">
                            <div>
                                <small class="block text-[11px] font-bold text-slate-400 uppercase">Mulai Dari</small>
                                <span class="text-lg font-black text-teal-700">Rp {{ number_format($pkg->price, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-500">/pax</span>
                            </div>

                            <a 
                                href="{{ route('packages.show', $pkg->slug) }}" 
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shadow-xs no-underline"
                            >
                                <span>Lihat Detail</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center text-slate-500">
                        <strong class="text-lg text-slate-800 block mb-1">Paket Wisata Tidak Ditemukan</strong>
                        Tidak ada paket wisata yang cocok dengan kriteria pencarian Anda. Silakan coba kata kunci lain.
                    </div>
                @endforelse
            </div>

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

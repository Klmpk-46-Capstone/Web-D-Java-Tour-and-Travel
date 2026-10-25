{{-- REKOMENDASI UTAMA (FEATURED PACKAGES DARI CMS) --}}
@if(isset($featuredPackages) && $featuredPackages->isNotEmpty())
<section class="py-6 pb-10 md:py-8 md:pb-12" id="rekomendasi-utama">
    <div class="container mx-auto px-4">
        <div class="mb-5 flex flex-col items-start gap-2 md:mb-7 md:flex-row md:items-end md:justify-between md:gap-7">
            <div>
                <span class="mb-1.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800">
                    <span>✦</span> TOP TRAVEL RECOMMENDATIONS
                </span>
                <h2 class="m-0 text-3xl leading-tight tracking-tight md:text-4xl">Most Popular Featured Packages</h2>
            </div>
            <p class="mb-0 mt-0 max-w-md text-sm leading-relaxed text-slate-500 md:mb-0.5">
                Curated top travel routes with the highest satisfaction, guided by the experienced D'Java Tour team.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($featuredPackages as $pkg)
                <article class="group bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="relative h-60 overflow-hidden bg-slate-100">
                            <img 
                                src="{{ $pkg->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80' }}" 
                                alt="{{ $pkg->name }}" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                            {{-- Badge Top --}}
                            <div class="absolute top-3 left-3 flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-teal-600 text-white shadow-sm">
                                    {{ $pkg->badge ?? 'FEATURED TRIP' }}
                                </span>
                            </div>

                            {{-- Rating Top Right --}}
                            <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-full text-xs font-bold text-slate-900 flex items-center gap-1 shadow-xs">
                                <span class="text-amber-500">★</span>
                                <span>{{ $pkg->rating }}</span>
                                <span class="text-slate-400 font-normal">({{ $pkg->total_reviews }})</span>
                            </div>

                            {{-- Bottom Info on image --}}
                            <div class="absolute bottom-3 left-3 right-3 text-white">
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-200 block mb-0.5">
                                    {{ $pkg->category }}
                                </span>
                                <h3 class="text-lg font-extrabold text-white leading-tight">
                                    {{ $pkg->name }}
                                </h3>
                            </div>
                        </div>

                        <div class="p-5">
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">
                                {{ $pkg->description }}
                            </p>

                            {{-- Itinerary highlights --}}
                            <div class="space-y-1.5 mb-4 bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
                                <div class="flex items-center justify-between text-slate-600">
                                    <span class="text-slate-400">Trip Duration:</span>
                                    <strong class="text-slate-800">{{ $pkg->duration }}</strong>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span class="text-slate-400">Next Departure:</span>
                                    <span class="font-bold text-teal-700">
                                        @if($pkg->trips->isNotEmpty())
                                            {{ $pkg->trips->first()->departure_date->translatedFormat('d M Y') }}
                                        @else
                                            Available Weekly
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 pt-0 flex items-center justify-between border-t border-slate-100 mt-2">
                        <div>
                            <small class="block text-[11px] font-bold text-slate-400 uppercase">Starting Price</small>
                            <span class="text-lg font-black text-teal-700">Rp {{ number_format($pkg->price, 0, '.', ',') }}</span>
                            <span class="text-xs text-slate-500">/pax</span>
                        </div>

                        <a 
                            href="{{ localized_route('packages.show', $pkg->slug) }}" 
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shadow-xs no-underline"
                        >
                            <span>Details &amp; Booking</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

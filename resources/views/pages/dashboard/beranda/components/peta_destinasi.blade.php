@php
    $isEn = $isEn ?? (app()->getLocale() === 'en');
    $rawWhatsapp = (string) config('services.djava.whatsapp', '');
    $whatsappNumber = preg_replace('/\D+/', '', $rawWhatsapp);

    $defaultDestinations = [
        'bromo' => [
            'id' => 'bromo',
            'name' => 'Gunung Bromo',
            'name_en' => 'Mount Bromo',
            'area' => 'Bromo Tengger Semeru',
            'area_en' => 'Bromo Tengger Semeru',
            'badge' => 'Mountain & Sunrise',
            'badge_en' => 'Mountain & Sunrise',
            'description' => 'Kawasan kaldera Bromo dengan lautan pasir legendaris, kawah aktif, dan panorama golden sunrise terbaik di Jawa Timur.',
            'description_en' => 'The Bromo caldera area featuring the legendary sea of sand, active crater, and the best golden sunrise view in East Java.',
            'x' => 53,
            'y' => 53.5,
            'label_pos' => 'top',
            'packages' => collect(),
        ],
        'tumpak_sewu' => [
            'id' => 'tumpak_sewu',
            'name' => 'Air Terjun Tumpak Sewu',
            'name_en' => 'Tumpak Sewu Waterfall',
            'area' => 'Pronojiwo, Lumajang',
            'area_en' => 'Pronojiwo, Lumajang',
            'badge' => 'Air Terjun Eksotis',
            'badge_en' => 'Exotic Waterfall',
            'description' => 'Air terjun tirai megah setinggi 120 meter di perbatasan Malang-Lumajang yang sering dijuluki Niagara-nya Indonesia.',
            'description_en' => 'A magnificent 120-meter curtain waterfall on the border of Malang and Lumajang, often dubbed the Niagara of Indonesia.',
            'x' => 55,
            'y' => 70.5,
            'label_pos' => 'top',
            'packages' => collect(),
        ],
        'ijen' => [
            'id' => 'ijen',
            'name' => 'Kawah Ijen',
            'name_en' => 'Ijen Crater',
            'area' => 'Banyuwangi & Bondowoso',
            'area_en' => 'Banyuwangi & Bondowoso',
            'badge' => 'Blue Fire Phenomenon',
            'badge_en' => 'Blue Fire Phenomenon',
            'description' => 'Fenomena kawah asam terbesar di dunia dengan pancaran Blue Fire magis di tengah malam dan panorama danau toska alami.',
            'description_en' => 'The world\'s largest acidic crater lake phenomenon featuring magical blue fire at midnight and panoramic turquoise waters.',
            'x' => 85.2,
            'y' => 67.5,
            'label_pos' => 'right',
            'packages' => collect(),
        ],
    ];

    $destinationsList = $mapDestinations ?? $defaultDestinations;
@endphp

{{-- Typography Link for Plus Jakarta Sans --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<section id="peta-destinasi" class="py-6 md:py-8 bg-transparent" style="font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
    <div class="max-w-5xl mx-auto px-4">
        {{-- Minimalist Card Container --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 sm:p-5 md:p-6 transition-all">
            
            {{-- Section Header: Compact Title on Left, Brief Description on Right --}}
            <div class="mb-4 sm:mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-6 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-teal-50 text-[#0D9488] border border-teal-200/60">
                        <svg class="w-3 h-3 text-[#0D9488]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $isEn ? 'Interactive Map' : 'Peta Wisata Jawa Timur' }}
                    </span>
                    <h2 class="m-0 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 leading-snug">
                        {{ $isEn ? 'Explore East Java Tourist Destinations' : 'Eksplorasi Peta Wisata Jawa Timur' }}
                    </h2>
                </div>

                <p class="m-0 text-xs sm:text-sm text-slate-500 max-w-xs sm:max-w-sm sm:text-right leading-relaxed">
                    {{ $isEn
                        ? 'Click the destination points on the map to explore our best tour packages.'
                        : 'Klik titik destinasi pada peta untuk menemukan pilihan paket wisata terbaik kami.' }}
                </p>
            </div>

            {{-- Map Canvas Frame: Centered, Proportional, Constrained Height --}}
            <div class="relative mx-auto w-full max-w-[760px] aspect-[16/9] max-h-[420px] lg:max-h-[440px] overflow-hidden rounded-xl border border-slate-200/70 bg-slate-50 shadow-inner select-none">
                {{-- Map Illustration Image --}}
                <img
                    src="{{ asset('images/peta-jawa-timur.png') }}"
                    alt="{{ $isEn ? 'East Java Destination Map' : 'Peta Destinasi Wisata Jawa Timur' }}"
                    class="w-full h-full object-cover select-none pointer-events-none"
                    loading="lazy"
                >

                {{-- Interactive Minimalist Markpoints --}}
                @foreach($destinationsList as $destKey => $dest)
                    @php
                        $destName = $isEn ? ($dest['name_en'] ?? $dest['name']) : $dest['name'];
                        $destArea = $isEn ? ($dest['area_en'] ?? $dest['area']) : $dest['area'];
                        $pkgCount = isset($dest['packages']) ? count($dest['packages']) : 0;
                        $posX = $dest['x'] ?? 50;
                        $posY = $dest['y'] ?? 50;
                        $labelPos = $dest['label_pos'] ?? 'top';

                        // Label badge placement
                        $labelClass = match($labelPos) {
                            'right' => 'left-full ml-1.5 sm:ml-2 top-1/2 -translate-y-1/2',
                            'bottom' => 'top-full mt-1.5 left-1/2 -translate-x-1/2',
                            default => 'bottom-full mb-1.5 left-1/2 -translate-x-1/2',
                        };
                    @endphp

                    <button
                        type="button"
                        class="map-pin-btn absolute -translate-x-1/2 -translate-y-1/2 group z-20 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0D9488]/40 rounded-full select-none"
                        style="left: {{ $posX }}%; top: {{ $posY }}%;"
                        data-destination="{{ $destKey }}"
                        aria-label="{{ $destName }}"
                    >
                        {{-- Subtle Pulse Ring --}}
                        <span class="absolute -inset-1.5 sm:-inset-2 rounded-full bg-[#0D9488]/30 animate-ping pointer-events-none"></span>

                        {{-- Solid Core Dot in #0D9488 with thin white ring --}}
                        <span class="relative flex items-center justify-center w-3.5 h-3.5 sm:w-4 sm:h-4 rounded-full bg-[#0D9488] ring-2 ring-white shadow-md group-hover:scale-125 transition-transform duration-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        </span>

                        {{-- Compact Permanent Destination Label Pill with Hover Highlight --}}
                        <span class="absolute {{ $labelClass }} pointer-events-auto whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold tracking-tight bg-white/95 text-slate-800 shadow-xs border border-slate-200/90 group-hover:bg-[#0D9488] group-hover:text-white group-hover:border-[#0D9488] group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#0D9488] group-hover:bg-white transition-colors"></span>
                                <span>{{ $destName }}</span>
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Quick Destination Switcher Pills --}}
            <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <span class="text-slate-400 font-medium flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0D9488]"></span>
                    <span>{{ $isEn ? 'Quick Select:' : 'Pilihan Cepat:' }}</span>
                </span>

                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    @foreach($destinationsList as $destKey => $dest)
                        @php
                            $destName = $isEn ? ($dest['name_en'] ?? $dest['name']) : $dest['name'];
                            $pkgCount = isset($dest['packages']) ? count($dest['packages']) : 0;
                        @endphp
                        <button
                            type="button"
                            class="map-chip-btn inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 hover:bg-teal-50 hover:text-[#0D9488] text-slate-700 border border-slate-200/80 transition cursor-pointer"
                            data-destination="{{ $destKey }}"
                        >
                            <span>{{ $destName }}</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-white text-slate-500 border border-slate-200 font-normal">
                                {{ $pkgCount }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Minimalist Interactive Destination Modal --}}
    <div
        id="map-destination-modal"
        class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300 hidden opacity-0 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="map-modal-title"
    >
        <div
            id="map-destination-modal-card"
            class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-xl w-full max-h-[85vh] flex flex-col overflow-hidden border border-slate-100 transform scale-95 transition-all duration-300"
        >
            {{-- Modal Header --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-start justify-between gap-3 bg-gradient-to-r from-teal-50/60 via-white to-slate-50">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0D9488] text-white flex items-center justify-center shadow-sm shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <span id="map-modal-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-[#0D9488] text-white tracking-wider">
                                Destinasi Wisata
                            </span>
                            <span id="map-modal-area" class="text-xs font-medium text-slate-500">
                                Jawa Timur
                            </span>
                        </div>
                        <h3 id="map-modal-title" class="m-0 text-lg sm:text-xl font-bold text-slate-900 leading-tight">
                            Nama Destinasi
                        </h3>
                    </div>
                </div>

                {{-- Close Button (X) --}}
                <button
                    type="button"
                    id="map-modal-close-btn"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer focus:outline-none"
                    aria-label="{{ $isEn ? 'Close Modal' : 'Tutup Dialog' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1">
                <p id="map-modal-description" class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                    Deskripsi destinasi...
                </p>

                <div class="pt-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center justify-between">
                        <span>{{ $isEn ? 'Available Tour Packages' : 'Pilihan Paket Wisata Tersedia' }}</span>
                        <span id="map-modal-package-count" class="text-[#0D9488] font-bold"></span>
                    </h4>

                    {{-- Dynamic Panels for each destination --}}
                    @foreach($destinationsList as $destKey => $dest)
                        @php
                            $packages = $dest['packages'] ?? collect();
                            $destName = $isEn ? ($dest['name_en'] ?? $dest['name']) : $dest['name'];
                            $waText = rawurlencode(
                                $isEn
                                    ? "Hello D'Java Tour, I am interested in travel packages to {$destName}. Can I get info on schedules or custom trips?"
                                    : "Halo D'Java Tour & Travel, saya tertarik dengan informasi paket wisata ke {$destName}. Apakah ada jadwal open trip atau opsi custom/private trip?"
                            );
                            $destWaUrl = !empty($whatsappNumber) ? "https://wa.me/{$whatsappNumber}?text={$waText}" : null;
                        @endphp

                        <div id="dest-panel-{{ $destKey }}" class="dest-panel hidden space-y-3">
                            @if($packages->isNotEmpty())
                                @foreach($packages as $pkg)
                                    @php
                                        $pkgUrl = function_exists('localized_route')
                                            ? localized_route('packages.show', $pkg->slug)
                                            : route('packages.show', $pkg->slug);
                                    @endphp
                                    <article class="bg-white rounded-xl border border-slate-200 p-3 sm:p-3.5 hover:border-teal-400 hover:shadow-sm transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3.5">
                                        <div class="flex items-center gap-3 w-full sm:w-auto">
                                            <div class="relative w-16 h-16 sm:w-18 sm:h-18 rounded-lg overflow-hidden bg-slate-100 shrink-0">
                                                <img
                                                    src="{{ $pkg->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=400&q=80' }}"
                                                    alt="{{ $pkg->name }}"
                                                    class="w-full h-full object-cover"
                                                    loading="lazy"
                                                >
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#0D9488] text-white">
                                                        {{ $pkg->badge ?? 'POPULER' }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-400 font-medium">
                                                        {{ $pkg->duration }}
                                                    </span>
                                                </div>
                                                <h5 class="m-0 text-sm font-bold text-slate-900 leading-snug">
                                                    {{ $pkg->name }}
                                                </h5>
                                                <div class="mt-0.5 flex items-center gap-1 text-xs text-amber-500 font-bold">
                                                    <span>★ {{ $pkg->rating }}</span>
                                                    <span class="text-slate-400 font-normal">({{ $pkg->total_reviews }})</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 gap-2">
                                            <div class="text-left sm:text-right">
                                                <small class="block text-[10px] font-bold text-slate-400 uppercase">
                                                    {{ $isEn ? 'Starting from' : 'Mulai dari' }}
                                                </small>
                                                <span class="text-sm sm:text-base font-extrabold text-[#0D9488]">
                                                    Rp {{ number_format($pkg->price, 0, $isEn ? '.' : ',', $isEn ? ',' : '.') }}
                                                </span>
                                            </div>
                                            <a
                                                href="{{ $pkgUrl }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#0D9488] hover:bg-teal-800 text-white text-xs font-bold transition shadow-xs no-underline"
                                            >
                                                <span>{{ $isEn ? 'View Details' : 'Lihat Detail' }}</span>
                                                <span>&rarr;</span>
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            @else
                                {{-- Friendly & Polite Empty State --}}
                                <div class="text-center py-6 px-4 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                    <div class="w-11 h-11 mx-auto rounded-full bg-teal-100 text-[#0D9488] flex items-center justify-center mb-2.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <h5 class="text-sm font-bold text-slate-900 mb-1">
                                        {{ $isEn ? 'Tour Package Currently in Preparation' : 'Paket Wisata Sedang Disiapkan' }}
                                    </h5>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-3.5 leading-relaxed">
                                        {{ $isEn
                                            ? 'Tour packages for this destination are currently being prepared by our team. Looking for a custom or private trip schedule? Chat directly with our customer service.'
                                            : 'Paket perjalanan untuk destinasi ini sedang disiapkan oleh tim D\'Java Tour. Ingin jadwal custom atau private trip untuk destinasi ini? Silakan konsultasi via WhatsApp.' }}
                                    </p>
                                    @if($destWaUrl)
                                        <a
                                            href="{{ $destWaUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs no-underline"
                                        >
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                            <span>{{ $isEn ? 'Consult via WhatsApp' : 'Konsultasi via WhatsApp' }}</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>D'Java Tour &amp; Travel</span>
                </span>
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold transition cursor-pointer"
                    id="map-modal-close-footer"
                >
                    {{ $isEn ? 'Close' : 'Tutup' }}
                </button>
            </div>
        </div>
    </div>
</section>

{{-- Client-side Map Interaction Logic --}}
<script>
    (function () {
        const destinationsData = @json($destinationsList);
        const isEn = {{ $isEn ? 'true' : 'false' }};

        const modal = document.getElementById('map-destination-modal');
        const modalCard = document.getElementById('map-destination-modal-card');
        const modalTitle = document.getElementById('map-modal-title');
        const modalBadge = document.getElementById('map-modal-badge');
        const modalArea = document.getElementById('map-modal-area');
        const modalDesc = document.getElementById('map-modal-description');
        const modalCount = document.getElementById('map-modal-package-count');

        const closeBtn = document.getElementById('map-modal-close-btn');
        const closeFooterBtn = document.getElementById('map-modal-close-footer');
        const pinButtons = document.querySelectorAll('.map-pin-btn');
        const chipButtons = document.querySelectorAll('.map-chip-btn');
        const allPanels = document.querySelectorAll('.dest-panel');

        function openDestinationModal(destKey) {
            const data = destinationsData[destKey];
            if (!data) return;

            // Set Header Information
            const title = isEn ? (data.name_en || data.name) : data.name;
            const badge = isEn ? (data.badge_en || data.badge) : data.badge;
            const area = isEn ? (data.area_en || data.area) : data.area;
            const desc = isEn ? (data.description_en || data.description) : data.description;
            const count = data.packages ? data.packages.length : 0;

            modalTitle.textContent = title;
            modalBadge.textContent = badge;
            modalArea.textContent = area;
            modalDesc.textContent = desc;
            modalCount.textContent = count + ' ' + (isEn ? (count === 1 ? 'Package' : 'Packages') : 'Paket');

            // Hide all panels & reveal target panel
            allPanels.forEach(p => p.classList.add('hidden'));
            const targetPanel = document.getElementById('dest-panel-' + destKey);
            if (targetPanel) {
                targetPanel.classList.remove('hidden');
            }

            // Show Modal with Animation
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                modalCard.classList.remove('scale-95');
                modalCard.classList.add('scale-100');
            });
            document.body.style.overflow = 'hidden';
        }

        function closeDestinationModal() {
            modal.classList.add('opacity-0');
            modalCard.classList.remove('scale-100');
            modalCard.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 200);
        }

        pinButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const destKey = this.getAttribute('data-destination');
                openDestinationModal(destKey);
            });
        });

        chipButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const destKey = this.getAttribute('data-destination');
                openDestinationModal(destKey);
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeDestinationModal);
        if (closeFooterBtn) closeFooterBtn.addEventListener('click', closeDestinationModal);

        // Click outside modal card to close
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeDestinationModal();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeDestinationModal();
            }
        });
    })();
</script>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $package->name }} - D'Java Tour &amp; Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', Arial, sans-serif; }
    </style>
</head>
<body class="bg-indigo-50/40 text-slate-800 antialiased min-h-screen flex flex-col">
    @include('components.navbar.navbar')

    <main class="flex-1 py-8 md:py-12">
        <div class="container mx-auto px-4">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-900">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-900">&times;</button>
                </div>
            @endif

            {{-- HERO HEADER --}}
            <div class="relative h-72 sm:h-96 md:h-[420px] rounded-3xl overflow-hidden mb-8 bg-slate-900 shadow-lg">
                <img 
                    src="{{ $package->image_url ?? 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1600&q=80' }}" 
                    alt="{{ $package->name }}" 
                    class="w-full h-full object-cover opacity-85"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>

                <div class="absolute bottom-6 left-6 right-6 md:bottom-10 md:left-10 md:right-10 text-white">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-teal-600 text-white shadow-sm">
                            {{ $package->badge ?? $package->category }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-xs text-white">
                            {{ $package->duration }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-slate-950 flex items-center gap-1">
                            ★ {{ $package->rating }} ({{ $package->total_reviews }} Ulasan)
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        {{ $package->name }}
                    </h1>
                </div>
            </div>

            {{-- MAIN CONTENT GRID --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- LEFT: DETAIL & ITINERARY & REVIEWS (8 cols) --}}
                <div class="lg:col-span-8 space-y-8">
                    {{-- Deskripsi Paket --}}
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                        <h2 class="text-xl font-extrabold text-slate-900 mb-3">Tentang Perjalanan Ini</h2>
                        <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                            {{ $package->description }}
                        </p>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block font-bold uppercase">Kategori</span>
                                <strong class="text-slate-800">{{ $package->category }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-bold uppercase">Durasi Trip</span>
                                <strong class="text-slate-800">{{ $package->duration }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-bold uppercase">Rute Wilayah</span>
                                <strong class="text-teal-700 uppercase">{{ $package->route_type }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Point 1: Itinerary Lengkap --}}
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-teal-700 block mb-0.5">TIMELINE KEGIATAN</span>
                                <h2 class="text-xl font-extrabold text-slate-900">Rencana Perjalanan (Itinerary)</h2>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800">
                                {{ $package->itineraries->count() }} Hari Tur
                            </span>
                        </div>

                        <div class="relative border-l-2 border-teal-200 ml-4 pl-6 space-y-6">
                            @forelse ($package->itineraries as $it)
                                <div class="relative">
                                    <div class="absolute -left-[33px] top-0 grid h-6 w-6 place-items-center rounded-full bg-teal-600 text-white text-[11px] font-black ring-4 ring-white">
                                        {{ $it->day_number }}
                                    </div>
                                    <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                                        <div class="text-xs font-extrabold text-teal-700 uppercase tracking-wide mb-1">
                                            HARI KE-{{ $it->day_number }}
                                        </div>
                                        <h3 class="text-base font-extrabold text-slate-900 mb-2">
                                            {{ $it->title }}
                                        </h3>
                                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                            {{ $it->description }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400">Rincian itinerary belum diinput.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Point 5: Moderasi Ulasan (Customer Reviews) --}}
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-teal-700 block mb-0.5">TESTIMONI ASLI</span>
                                <h2 class="text-xl font-extrabold text-slate-900">Ulasan &amp; Pengalaman Peserta</h2>
                            </div>
                            <div class="flex items-center gap-1.5 font-extrabold text-lg text-slate-900">
                                <span class="text-amber-500">★</span>
                                <span>{{ $package->rating }}</span>
                                <span class="text-xs font-normal text-slate-400">/ 5.0</span>
                            </div>
                        </div>

                        {{-- Reviews List (Only visible / approved reviews) --}}
                        <div class="space-y-4 mb-8">
                            @forelse ($package->reviews as $rev)
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div class="flex items-center justify-between mb-1">
                                        <strong class="text-sm font-bold text-slate-900">{{ $rev->customer_name }}</strong>
                                        <span class="text-amber-500 text-xs">
                                            @for ($i = 0; $i < $rev->rating; $i++) ★ @endfor
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                        {{ $rev->comment }}
                                    </p>
                                    <div class="text-[10px] text-slate-400 mt-2">
                                        Diverifikasi oleh D'Java Tour System • {{ $rev->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 rounded-2xl bg-slate-50 text-center text-xs text-slate-400">
                                    Belum ada ulasan terpublikasi untuk paket ini. Jadilah yang pertama memberikan review!
                                </div>
                            @endforelse
                        </div>

                        {{-- Review Submission Form --}}
                        <div class="pt-6 border-t border-slate-100">
                            <h3 class="text-sm font-extrabold text-slate-900 mb-3">Tulis Ulasan Anda</h3>
                            <form action="{{ route('packages.review.store', $package->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                                        <input type="text" name="customer_name" required placeholder="Nama Anda" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-teal-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Rating Kepuasan</label>
                                        <select name="rating" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-teal-700">
                                            <option value="5">★★★★★ (5/5) Sangat Puas</option>
                                            <option value="4">★★★★☆ (4/5) Puas</option>
                                            <option value="3">★★★☆☆ (3/5) Cukup</option>
                                            <option value="2">★★☆☆☆ (2/5) Kurang</option>
                                            <option value="1">★☆☆☆☆ (1/5) Tidak Puas</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Ulasan / Testimoni</label>
                                    <textarea name="comment" required rows="2" placeholder="Ceritakan pengalaman perjalanan Anda..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:bg-white focus:outline-none focus:border-teal-700"></textarea>
                                </div>

                                <button type="submit" class="px-5 py-2.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition">
                                    Kirim Ulasan ke Moderasi CMS
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: DYNAMIC BOOKING FORM & SEAT QUOTA (4 cols) --}}
                <div class="lg:col-span-4">
                    <div class="sticky top-24 bg-white rounded-3xl border-2 border-teal-600/30 p-6 shadow-xl space-y-6">
                        {{-- Price Header --}}
                        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <small class="text-xs uppercase font-bold text-slate-400 block">Harga Paket</small>
                                <span class="text-2xl font-black text-teal-800">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-500">/pax</span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-teal-50 text-teal-800">
                                Instant Booking
                            </span>
                        </div>

                        {{-- Point 3: Form Booking dengan Kuota Dinamis --}}
                        <form action="{{ route('booking.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                    <span>Pilih Tanggal Batch Trip:</span>
                                    <span class="text-[10px] text-teal-700 font-extrabold">TERHUBUNG CMS</span>
                                </label>
                                <select 
                                    name="trip_id" 
                                    id="tripSelector"
                                    required 
                                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-xs focus:bg-white focus:outline-none focus:border-teal-700 font-medium"
                                >
                                    @forelse ($package->trips as $trip)
                                        @php
                                            $remaining = $trip->remaining_seats;
                                            $disabled = $remaining <= 0;
                                        @endphp
                                        <option 
                                            value="{{ $trip->id }}" 
                                            {{ $disabled ? 'disabled' : '' }}
                                            data-seats="{{ $remaining }}"
                                        >
                                            {{ $trip->departure_date->translatedFormat('d M Y') }} - {{ $trip->batch_name }} 
                                            @if ($disabled)
                                                [HABIS / FULL]
                                            @else
                                                (Sisa {{ $remaining }} Kursi)
                                            @endif
                                        </option>
                                    @empty
                                        <option disabled selected>Belum ada jadwal batch trip aktif</option>
                                    @endforelse
                                </select>
                                <span class="text-[11px] text-slate-400 block mt-1">
                                    *Jadwal dengan kuota habis otomatis terkunci (disabled) oleh sistem.
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Pax</label>
                                    <input 
                                        type="number" 
                                        name="pax" 
                                        id="inputPax"
                                        min="1" 
                                        max="20" 
                                        value="1" 
                                        required 
                                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:bg-white focus:outline-none focus:border-teal-700"
                                        oninput="calculateTotal()"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Metode Bayar</label>
                                    <select name="payment_method" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-teal-700">
                                        <option value="BCA Virtual Account">BCA Virtual Account</option>
                                        <option value="Mandiri VA">Mandiri VA</option>
                                        <option value="BRI VA">BRI VA</option>
                                        <option value="QRIS Dinamis">QRIS Dinamis</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pemesan</label>
                                <input type="text" name="customer_name" value="{{ auth()->user()?->name ?? 'Rian Adiputra' }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-teal-700">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                                    <input type="email" name="customer_email" value="{{ auth()->user()?->email ?? 'rian.adi@gmail.com' }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-teal-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp</label>
                                    <input type="text" name="customer_phone" value="{{ auth()->user()?->whatsapp ?? '+62 812-9988-1122' }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:bg-white focus:outline-none focus:border-teal-700">
                                </div>
                            </div>

                            {{-- Total Calculation Display --}}
                            <div class="p-3 bg-teal-50 rounded-2xl border border-teal-100 flex items-center justify-between text-xs">
                                <span class="font-bold text-teal-900">Estimasi Total Bayar:</span>
                                <span id="displayTotal" class="text-base font-black text-teal-800">
                                    Rp {{ number_format($package->price, 0, ',', '.') }}
                                </span>
                            </div>

                            <button 
                                type="submit" 
                                class="w-full py-3.5 px-4 bg-teal-700 hover:bg-teal-800 text-white font-extrabold rounded-2xl shadow-md transition text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Konfirmasi &amp; Bayar Sekarang
                            </button>
                        </form>

                        <div class="text-[11px] text-slate-400 text-center">
                            Didukung konfirmasi tiket instan &amp; integrasi manifes CMS real-time.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    @include('components.footer.footer')

    <script>
        const unitPrice = {{ $package->price }};
        function calculateTotal() {
            const pax = parseInt(document.getElementById('inputPax').value) || 1;
            const total = unitPrice * pax;
            document.getElementById('displayTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>

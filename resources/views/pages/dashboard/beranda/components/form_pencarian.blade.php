{{-- FORM PENCARIAN --}}
<section class="py-16 pb-12 text-center md:px-0 md:py-24 md:pb-20">
    <div class="container mx-auto px-4">
        <h1 class="mx-auto mb-3 max-w-5xl text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl md:text-6xl">
            Eksplorasi Jawa Timur
            <span class="block italic text-teal-700">Tanpa Kompromi</span>
        </h1>

        <p class="mx-auto max-w-3xl text-sm leading-relaxed text-slate-500 md:text-base">
            Rental armada privat dan perjalanan wisata Jawa Timur,
            dari Bromo hingga Ijen dan Tumpak Sewu,
            sesuai kebutuhan perjalananmu.
        </p>

        <form class="mx-auto mt-7 max-w-6xl rounded-2xl border border-slate-100 bg-white p-4 text-left shadow-lg md:mt-10 md:p-6" action="{{ route('home') }}" method="GET">
            <div class="mb-4 flex gap-2">
                <span class="inline-flex items-center rounded-full bg-teal-700 px-4 py-2 text-xs font-bold text-white">Sewa Mobil Privat</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600">Paket Petualangan</span>
            </div>

            <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-2 xl:grid-cols-[1.15fr_1.15fr_1fr_0.85fr_4rem]">
                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="pickup">Titik Penjemputan</label>
                    <input class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none placeholder:text-slate-400" type="text" id="pickup" name="pickup" value="{{ request('pickup') }}" placeholder="Contoh: Bandara Juanda" maxlength="150">
                </div>

                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="destination">Destinasi / Rute</label>
                    <select class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none" id="destination" name="destination">
                        <option value="">Pilih destinasi</option>
                        @foreach ($destinations as $destination)
                            <option value="{{ $destination->id }}" @selected((string) request('destination') === (string) $destination->id)>{{ $destination->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="departure_date">Waktu Berangkat</label>
                    <input class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none" type="date" id="departure_date" name="departure_date" value="{{ request('departure_date') }}" min="{{ now()->toDateString() }}">
                </div>

                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="passengers">Kapasitas</label>
                    <select class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none" id="passengers" name="passengers">
                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 10, 12, 15] as $count)
                            <option value="{{ $count }}" @selected((int) request('passengers', 4) === $count)>{{ $count }} orang</option>
                        @endforeach
                    </select>
                </div>

                <button class="grid min-h-14 place-items-center rounded-xl border-0 bg-teal-700 text-white transition hover:bg-teal-800 xl:min-h-0" type="submit" aria-label="Cari perjalanan">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                </button>
            </div>
            <p class="mb-0 mt-3 text-xs text-slate-500">Pilihan destinasi diambil dari database D'Java Tour &amp; Travel.</p>
        </form>
    </div>
</section>

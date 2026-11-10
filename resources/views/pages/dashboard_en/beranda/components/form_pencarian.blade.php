{{-- FORM PENCARIAN --}}
<section class="py-16 pb-12 text-center md:px-0 md:py-24 md:pb-20">
    <div class="container mx-auto px-4">
        <h1 class="mx-auto mb-3 max-w-5xl text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl md:text-6xl">
            Explore East Java
            <span class="block italic text-teal-700">Without Compromise</span>
        </h1>

        <p class="mx-auto max-w-3xl text-sm leading-relaxed text-slate-500 md:text-base">
            Private vehicle rental and East Java tour packages,
            from Bromo to Ijen and Tumpak Sewu,
            tailored to your travel needs.
        </p>

        <form class="mx-auto mt-7 max-w-6xl rounded-2xl border border-slate-100 bg-white p-4 text-left shadow-lg md:mt-10 md:p-6" action="{{ localized_route('home') }}#hasil-pencarian" method="GET">
            <input type="hidden" name="search" value="1">

            <div class="mb-4 flex flex-wrap gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="mode" value="paket" class="peer sr-only" @checked($mode === 'paket')>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-teal-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-teal-700 peer-focus-visible:ring-offset-2">
                        Tour Packages
                    </span>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="mode" value="mobil" class="peer sr-only" @checked($mode === 'mobil')>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-teal-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-teal-700 peer-focus-visible:ring-offset-2">
                        Private Car Rental
                    </span>
                </label>
            </div>

            <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-2 xl:grid-cols-[1.3fr_1.15fr_1fr_4rem]">
                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="pickup" data-search-label data-label-paket="Search Packages" data-label-mobil="Search Vehicles">{{ $mode === 'mobil' ? 'Search Vehicles' : 'Search Packages' }}</label>
                    <input class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none placeholder:text-slate-400" type="text" id="pickup" name="pickup" value="{{ request('pickup') }}" placeholder="{{ $mode === 'mobil' ? 'Example: Innova or Hiace' : 'Example: Bromo or Dieng' }}" data-ph-paket="Example: Bromo or Dieng" data-ph-mobil="Example: Innova or Hiace" maxlength="150">
                </div>

                {{-- Khusus Paket Wisata --}}
                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white @if ($mode !== 'paket') hidden @endif" data-mode-field="paket">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="destination">Destination</label>
                    <select class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none" id="destination" name="destination" @disabled($mode !== 'paket')>
                        <option value="">Select destination</option>
                        @foreach ($destinationOptions as $destination)
                            <option value="{{ $destination }}" @selected(request('destination') === $destination)>{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white @if ($mode !== 'paket') hidden @endif" data-mode-field="paket">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="departure_date">Departure Date</label>
                    <input class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none" type="date" id="departure_date" name="departure_date" value="{{ request('departure_date') }}" min="{{ now()->toDateString() }}" @disabled($mode !== 'paket')>
                </div>

                {{-- Khusus Sewa Mobil Privat --}}
                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white @if ($mode !== 'mobil') hidden @endif" data-mode-field="mobil">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="transmission">Transmission</label>
                    <select class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none" id="transmission" name="transmission" @disabled($mode !== 'mobil')>
                        <option value="">Select transmission</option>
                        <option value="manual" @selected(request('transmission') === 'manual')>Manual</option>
                        <option value="automatic" @selected(request('transmission') === 'automatic')>Automatic</option>
                    </select>
                </div>

                <div class="min-w-0 rounded-xl border border-transparent bg-slate-50 px-4 py-3 transition focus-within:border-teal-300 focus-within:bg-white @if ($mode !== 'mobil') hidden @endif" data-mode-field="mobil">
                    <label class="mb-1.5 block text-xs font-extrabold uppercase tracking-wide text-slate-500" for="passengers">Passenger Capacity</label>
                    <div class="flex items-center gap-2">
                        <button type="button" class="grid h-7 w-7 shrink-0 place-items-center rounded-lg border border-slate-200 bg-white text-base font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700" data-step="-1" aria-label="Decrease passengers">&minus;</button>
                        <input class="w-full min-w-0 border-0 bg-transparent p-0 text-sm font-semibold text-slate-900 outline-none text-center [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" type="number" inputmode="numeric" id="passengers" name="passengers" value="{{ max(1, min(50, (int) request('passengers', 4))) }}" min="1" max="50" @disabled($mode !== 'mobil')>
                        <button type="button" class="grid h-7 w-7 shrink-0 place-items-center rounded-lg border border-slate-200 bg-white text-base font-bold text-slate-700 transition hover:border-teal-300 hover:text-teal-700" data-step="1" aria-label="Increase passengers">+</button>
                        <span class="shrink-0 text-xs font-semibold text-slate-500">people</span>
                    </div>
                </div>

                <button class="grid min-h-14 place-items-center rounded-xl border-0 bg-teal-700 text-white transition hover:bg-teal-800 xl:min-h-0" type="submit" aria-label="Search packages or vehicles">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                </button>
            </div>
            <p class="mb-0 mt-3 text-xs text-slate-500" data-hint data-hint-paket="Type a tour package name, choose a destination, then pick a departure date." data-hint-mobil="Type a vehicle name, choose a transmission, then set the number of passengers.">{{ $mode === 'mobil' ? 'Type a vehicle name, choose a transmission, then set the number of passengers.' : 'Type a tour package name, choose a destination, then pick a departure date.' }}</p>

            <script>
                (function () {
                    const form = document.currentScript.closest('form');
                    if (!form) return;

                    const label = form.querySelector('[data-search-label]');
                    const input = form.querySelector('#pickup');
                    const hint = form.querySelector('[data-hint]');

                    function applyMode() {
                        const checked = form.querySelector('input[name="mode"]:checked');
                        const mode = checked ? checked.value : 'paket';
                        const key = mode === 'mobil' ? 'Mobil' : 'Paket';

                        form.querySelectorAll('[data-mode-field]').forEach(function (box) {
                            const active = box.dataset.modeField === mode;
                            box.classList.toggle('hidden', !active);
                            box.querySelectorAll('input, select').forEach(function (el) { el.disabled = !active; });
                        });

                        label.textContent = label.dataset['label' + key];
                        input.placeholder = input.dataset['ph' + key];
                        hint.textContent = hint.dataset['hint' + key];
                    }

                    form.querySelectorAll('input[name="mode"]').forEach(function (radio) {
                        radio.addEventListener('change', applyMode);
                    });

                    const qty = form.querySelector('#passengers');
                    form.querySelectorAll('[data-step]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const min = Number(qty.min) || 1;
                            const max = Number(qty.max) || 50;
                            const next = (parseInt(qty.value, 10) || min) + Number(btn.dataset.step);
                            qty.value = Math.min(max, Math.max(min, next));
                        });
                    });

                    applyMode();
                })();
            </script>
        </form>
    </div>
</section>

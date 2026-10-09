{{-- DESTINASI --}}
<section class="py-6 pb-10 md:py-8 md:pb-12" id="destinasi">
    <div class="container mx-auto px-4">
        <div class="mb-5 flex flex-col items-start gap-2 md:mb-7 md:flex-row md:items-end md:justify-between md:gap-7">
            <div>
                <span class="mb-1.5 block text-xs font-extrabold uppercase tracking-wider text-teal-700">KURASI LANSKAP</span>
                <h2 class="m-0 text-3xl leading-tight tracking-tight md:text-4xl">Destinasi Pilihan</h2>
            </div>
            <p class="mb-0 mt-0 max-w-md text-sm leading-relaxed text-slate-500 md:mb-0.5">
                Temukan destinasi wisata Jawa Timur untuk rencana
                perjalanan yang lebih mudah dan terarah.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6 xl:grid-cols-4">
            @forelse ($destinations as $destination)
                <article class="group min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="relative h-64 overflow-hidden bg-slate-100 sm:h-80 md:h-72 xl:h-80 2xl:h-96">
                        @php
                            $imagePath = data_get($destination, 'image_path');
                            $imageUrl = $destinationImage($imagePath);
                        @endphp
                        @if ($imageUrl)
                            <img class="block h-full w-full object-cover transition duration-500 group-hover:scale-105" src="{{ $imageUrl }}" alt="{{ $destination->name }}" loading="lazy">
                        @else
                            <div class="grid h-full w-full place-items-center px-6 py-10 text-center text-slate-500">Foto destinasi belum tersedia.</div>
                        @endif
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 top-1/3 bg-gradient-to-b from-transparent to-slate-900"></div>
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <span class="mb-1 block text-xs font-extrabold uppercase tracking-wide text-teal-200">{{ data_get($destination, 'category', 'DESTINASI WISATA') }}</span>
                            <h3 class="m-0 pr-10 text-xl leading-snug">{{ $destination->name }}</h3>
                            <span class="absolute bottom-0 right-0 grid h-9 w-9 place-items-center rounded-full bg-white/25" aria-hidden="true">↗</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-4 py-3">
                        <span class="whitespace-nowrap text-xs text-slate-600">
                            <svg class="mr-1 inline-block align-[-3px] text-teal-700" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            {{ data_get($destination, 'duration', 'Hubungi kami') }}
                        </span>
                        <div class="text-right">
                            <small class="block text-xs text-slate-400">Harga mulai dari</small>
                            @php
                                $destinationPrice = data_get($destination, 'price', data_get($destination, 'price_per_person'));
                            @endphp
                            @if ($destinationPrice !== null)
                                <strong class="text-sm text-teal-700">{{ $formatRupiah($destinationPrice) }}</strong>
                                <span class="text-xs text-slate-500">/orang</span>
                            @else
                                <strong class="text-sm text-teal-700">Konsultasi</strong>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-500">
                    <strong class="mb-2 block text-lg text-slate-900">Destinasi belum tersedia</strong>
                    Belum ada destinasi aktif di database. Silakan tambahkan data destinasi terlebih dahulu.
                </div>
            @endforelse
        </div>
    </div>
</section>

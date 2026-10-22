{{-- RINGKASAN HASIL PENCARIAN --}}
<section id="hasil-pencarian" class="pb-4 md:pb-6">
    <div class="container mx-auto px-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <p class="mb-1 text-xs font-extrabold uppercase tracking-wider text-teal-700">
                        Hasil Pencarian
                    </p>

                    <h2 class="m-0 text-2xl leading-tight tracking-tight md:text-3xl">
                        {{ $mode === 'paket' ? 'Paket Wisata' : 'Sewa Mobil Privat' }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                        @if ($pickup !== '')
                            <span class="inline-flex min-w-0 items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5">
                                <span class="font-bold text-slate-500">Kata kunci:</span>
                                <span class="min-w-0 break-words font-semibold text-slate-800">{{ $pickup }}</span>
                            </span>
                        @endif

                        @if ($selectedDestination)
                            <span class="inline-flex min-w-0 items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5">
                                <span class="font-bold text-slate-500">Rute:</span>
                                <span class="min-w-0 break-words font-semibold text-slate-800">{{ $selectedDestination->name }}</span>
                            </span>
                        @endif

                        @if ($departureDate)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5">
                                <span class="font-bold text-slate-500">Berangkat:</span>
                                <span class="font-semibold text-slate-800">
                                    {{ \Illuminate\Support\Carbon::parse($departureDate)->translatedFormat('d F Y') }}
                                </span>
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5">
                            <span class="font-bold text-slate-500">Kapasitas:</span>
                            <span class="font-semibold text-slate-800">{{ $passengers }} orang</span>
                        </span>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1.5 text-xs font-extrabold text-teal-800">
                        {{ $resultCount }} {{ $mode === 'paket' ? 'paket' : 'armada' }}
                    </span>

                    <a
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 no-underline transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800"
                        href="{{ route('home') }}"
                    >
                        Reset
                    </a>
                </div>
            </div>

            @if ($resultCount === 0)
                <p class="mb-0 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-relaxed text-amber-900">
                    Tidak ada hasil yang cocok. Coba ubah kata kunci, rute, tanggal, atau kapasitas.
                </p>
            @endif
        </div>
    </div>
</section>

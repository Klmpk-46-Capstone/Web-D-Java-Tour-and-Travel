<footer class="border-t border-slate-200 bg-indigo-50 py-12 pb-5">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 gap-8 pb-9 md:grid-cols-[1.7fr_0.9fr_1.2fr] md:gap-8 xl:gap-14">
            <div>
                <div class="mb-4 inline-flex items-center gap-2.5 text-slate-900">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-teal-100 bg-teal-50 text-2xl font-extrabold text-teal-700" aria-hidden="true">✦</span>
                    <span class="flex flex-col leading-tight">
                        <strong class="text-base tracking-tight">D'Java Tour &amp; Travel</strong>
                        <small class="mt-1 text-xs font-extrabold tracking-wider text-teal-700">EXPLORE EAST JAVA</small>
                    </span>
                </div>

                <p class="mb-5 mt-0 max-w-md text-sm leading-relaxed text-slate-500">
                    Layanan perjalanan dan rental kendaraan untuk
                    menjelajahi destinasi wisata Jawa Timur.
                    Hubungi tim kami untuk informasi layanan dan pemesanan.
                </p>

                <div class="flex flex-wrap gap-3.5">
                    <span class="text-xs font-bold text-teal-700">✓ Informasi jelas</span>
                    <span class="text-xs font-bold text-teal-700">✓ Konsultasi perjalanan</span>
                    <span class="text-xs font-bold text-teal-700">✓ Pilihan armada</span>
                </div>
            </div>

            <div>
                <h4 class="mb-4 mt-0 text-xs font-bold uppercase tracking-wide">Destinasi Populer</h4>
                @forelse ($destinations->take(4) as $destination)
                    <a class="mb-2.5 block break-words text-sm leading-relaxed text-slate-500 no-underline transition hover:text-teal-700" href="#destinasi">{{ $destination->name }}</a>
                @empty
                    <p class="mb-2.5 mt-0 text-sm leading-relaxed text-slate-500">Destinasi akan ditampilkan setelah data tersedia.</p>
                @endforelse
            </div>

            <div>
                <h4 class="mb-4 mt-0 text-xs font-bold uppercase tracking-wide">Kontak &amp; Informasi</h4>
                <p class="mb-2.5 mt-0 text-sm leading-relaxed text-slate-500">D'Java Tour &amp; Travel</p>
                @if ($whatsappUrl)
                    <a class="mb-2.5 block break-words text-sm leading-relaxed text-slate-500 no-underline transition hover:text-teal-700" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Hubungi WhatsApp Customer Service</a>
                @else
                    <p class="mb-2.5 mt-0 text-sm leading-relaxed text-slate-500">Nomor WhatsApp belum dikonfigurasi.</p>
                @endif
                <a class="mb-2.5 block text-sm leading-relaxed text-slate-500 no-underline transition hover:text-teal-700" href="#cara-booking">Cara Booking</a>
                <a class="mb-2.5 block text-sm leading-relaxed text-slate-500 no-underline transition hover:text-teal-700" href="#armada">Pilihan Armada</a>
            </div>
        </div>

        <div class="flex flex-col items-start justify-between gap-3 border-t border-slate-200 pt-5 text-xs text-slate-500 md:flex-row md:items-center">
            <span>&copy; {{ date('Y') }} D'Java Tour &amp; Travel.</span>
            <div class="flex flex-wrap gap-5">
                <a class="text-inherit no-underline hover:text-teal-700" href="#kontak">Informasi Layanan</a>
                <a class="text-inherit no-underline hover:text-teal-700" href="#kontak">Hubungi Kami</a>
            </div>
        </div>
    </div>
</footer>

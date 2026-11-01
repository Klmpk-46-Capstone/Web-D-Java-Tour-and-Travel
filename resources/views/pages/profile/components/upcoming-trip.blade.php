
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">

    @if ($upcomingBooking)

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

            {{-- Trip Image --}}
            <div class="h-20 w-full shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:w-20">

                @if (
                    filled($upcomingBooking->image_url)
                    && filter_var($upcomingBooking->image_url, FILTER_VALIDATE_URL)
                    && in_array(
                        parse_url($upcomingBooking->image_url, PHP_URL_SCHEME),
                        ['http', 'https'],
                        true
                    )
                )
                    <img
                        src="{{ $upcomingBooking->image_url }}"
                        alt="{{ $upcomingBooking->package_name }}"
                        class="h-full w-full object-cover"
                    >
                @else
                    <div class="flex h-full w-full items-center justify-center text-teal-700">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="m3 17 6-6 4 4 5-8 3 4"/>
                            <path d="M3 21h18"/>
                        </svg>
                    </div>
                @endif

            </div>

            {{-- Trip Details --}}
            <div class="min-w-0 flex-1">

                <p class="text-xs font-bold uppercase tracking-wide text-teal-700">
                    Perjalanan Mendatang
                </p>

                <h3 class="mt-1 text-base font-bold text-slate-900 sm:text-lg">
                    {{ $upcomingBooking->package_name ?? 'Perjalanan East Java Trans' }}
                </h3>

                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">

                    <span>
                        {{ \Illuminate\Support\Carbon::parse($upcomingBooking->departure_date)->translatedFormat('d M Y') }}
                    </span>

                    <span class="hidden text-slate-300 sm:inline">•</span>

                    <span>
                        {{ $upcomingBooking->pax }} Peserta
                    </span>

                </div>

                <p class="mt-1 text-xs text-slate-500">
                    Kode Booking: {{ $upcomingBooking->booking_code }}
                </p>

            </div>

            {{-- Detail Button --}}
            <div class="shrink-0">
                <a
                    href="{{ localized_route('customer.bookings.detail', ['bookingCode' => $upcomingBooking->booking_code]) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:bg-teal-50 hover:text-teal-800 sm:w-auto"
                >
                    Detail Pesanan

                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </div>

        </div>

    @else

        <div class="flex flex-col items-center justify-center px-5 py-10 text-center">

            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 text-teal-700">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                    <path d="m9 15 2 2 4-4"/>
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-bold text-slate-900">
                Belum Ada Perjalanan Mendatang
            </h3>

            <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                Kamu belum memiliki jadwal perjalanan yang akan datang. Jelajahi pilihan paket wisata dan armada kami.
            </p>

            <a
                href="{{ localized_route('packages.index') }}"
                class="mt-5 inline-flex items-center justify-center rounded-xl bg-teal-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-900"
            >
                Jelajahi Paket Wisata
            </a>

        </div>

    @endif

</div>

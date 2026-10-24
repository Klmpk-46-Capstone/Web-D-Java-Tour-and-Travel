<section class="rounded-2xl bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-5 sm:flex-row">
        <div class="h-32 w-full shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:w-40">
            @php
                $imageUrl = $booking->package->image_url ?? $booking->package->image ?? $booking->trip->image ?? null;
                $bookingTitle = $booking->package->name ?? $booking->trip->batch_name ?? $booking->selected_armada ?? 'Perjalanan Anda';
                $imageSrc = null;

                if (!empty($imageUrl)) {
                    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $scheme = strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME));

                        if (in_array($scheme, ['http', 'https'], true)) {
                            $imageSrc = $imageUrl;
                        }
                    } else {
                        $imagePath = ltrim($imageUrl, '/');

                        if (Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
                            $imageSrc = asset('storage/' . $imagePath);
                        }
                    }
                }
            @endphp

            @if ($imageSrc)
                <img
                    src="{{ $imageSrc }}"
                    alt="{{ $bookingTitle }}"
                    loading="lazy"
                    class="h-full w-full object-cover"
                    onerror="this.onerror=null; this.style.display='none';"
                >
            @else
                <div class="flex h-full items-center justify-center text-teal-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="m21 15-5-5L5 21"/>
                    </svg>
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                Trip Selesai
            </span>

            <h2 class="mt-2 text-xl font-bold text-slate-900">
                {{ $bookingTitle }}
            </h2>

            <div class="mt-3 grid gap-3 rounded-xl bg-blue-50 p-4 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-slate-500">No. Invoice</p>
                    <p class="mt-1 break-all font-semibold text-slate-800">
                        {{ $booking->booking_code }}
                    </p>
                </div>

                <div>
                    <p class="text-slate-500">Peserta</p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $booking->participant_count ?? 1 }} Peserta
                    </p>
                </div>

                <div>
                    <p class="text-slate-500">Armada</p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $booking->selected_armada ?? 'Sesuai pemesanan' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

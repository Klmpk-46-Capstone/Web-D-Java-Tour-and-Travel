@props(['review'])

@php
    $isEnglish = app()->getLocale() === 'en';

    $tagLabels = [
        'driver_ramah' => $isEnglish ? 'Friendly & Punctual Driver' : 'Driver Ramah & Tepat Waktu',
        'armada_bersih' => $isEnglish ? 'Clean Vehicle & Cold AC' : 'Armada Bersih & AC Dingin',
        'rute_tepat' => $isEnglish ? 'Accurate Travel Route' : 'Rute Perjalanan Tepat',
        'driver_berpengalaman' => $isEnglish ? 'Experienced Driver' : 'Driver Berpengalaman',
        'cs_responsif' => $isEnglish ? 'Fast Customer Service' : 'Respons CS Cepat',
        'mobil_nyaman' => $isEnglish ? 'Comfortable Car' : 'Mobil Nyaman',
    ];

    $serviceRatings = is_array($review->service_ratings)
        ? $review->service_ratings
        : [];

    $tags = is_array($review->experience_tags)
        ? $review->experience_tags
        : [];

    $photos = is_array($review->photos)
        ? $review->photos
        : [];
@endphp

<article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex min-w-0 items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-teal-50 text-sm font-extrabold text-teal-800">
                {{ strtoupper(mb_substr($review->customer_name ?? 'D', 0, 1)) }}
            </span>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="break-words font-bold text-slate-900">
                        {{ $review->customer_name }}
                    </h3>

                    @if ($review->identity_verified)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                            {{ $isEnglish ? 'Verified' : 'Terverifikasi' }}
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $review->created_at?->translatedFormat('d M Y') ?? ($isEnglish ? 'Date not available' : 'Tanggal tidak tersedia') }}
                </p>
            </div>
        </div>

        <span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-800">
            ★ {{ $review->rating }} / 5
        </span>
    </div>

    @if ($serviceRatings !== [])
        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs font-semibold text-slate-500">
            <span>{{ $isEnglish ? 'Vehicle' : 'Armada' }} {{ $serviceRatings['armada'] ?? '-' }}/5</span>
            <span aria-hidden="true">•</span>
            <span>Driver {{ $serviceRatings['driver'] ?? '-' }}/5</span>
            <span aria-hidden="true">•</span>
            <span>{{ $isEnglish ? 'Route' : 'Rute' }} {{ $serviceRatings['route'] ?? '-' }}/5</span>
        </div>
    @endif

    @if ($review->comment)
        <p class="mt-3 text-sm leading-6 text-slate-600">
            {{ $review->comment }}
        </p>
    @endif

    @if ($tags !== [])
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($tags as $tag)
                <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">
                    {{ $tagLabels[$tag] ?? \Illuminate\Support\Str::headline($tag) }}
                </span>
            @endforeach
        </div>
    @endif

    @if ($photos !== [])
        <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-5">
            @foreach ($photos as $photo)
                <a
                    href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block aspect-square overflow-hidden rounded-lg bg-slate-100"
                >
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo) }}"
                        alt="{{ $isEnglish ? 'Review photo from' : 'Foto ulasan dari' }} {{ $review->customer_name }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition duration-200 hover:scale-105"
                    >
                </a>
            @endforeach
        </div>
    @endif
</article>

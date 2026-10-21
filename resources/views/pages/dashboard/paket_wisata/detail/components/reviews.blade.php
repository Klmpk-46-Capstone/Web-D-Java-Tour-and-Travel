{{-- Reviews --}}
<section>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">
            Ulasan Pelanggan
        </h2>
        <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900">
            ★ {{ number_format($averageRating, 1) }} / 5
        </span>
    </div>

    <div class="space-y-4">
        @forelse ($reviews->take(5) as $review)
            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-900">
                            {{ $review->customer_name }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $review->created_at?->format('d M Y') ?? 'Tanggal tidak tersedia' }}
                        </p>
                    </div>

                    <span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-bold text-amber-800">
                        ★ {{ $review->rating }} / 5
                    </span>
                </div>

                @if ($review->comment)
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $review->comment }}
                    </p>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-600">
                Belum ada ulasan untuk paket wisata ini.
            </div>
        @endforelse
    </div>
</section>

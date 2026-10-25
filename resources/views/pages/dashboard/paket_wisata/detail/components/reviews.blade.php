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
            <x-reviews.card :review="$review" />
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-600">
                Belum ada ulasan untuk paket wisata ini.
            </div>
        @endforelse
    </div>
</section>

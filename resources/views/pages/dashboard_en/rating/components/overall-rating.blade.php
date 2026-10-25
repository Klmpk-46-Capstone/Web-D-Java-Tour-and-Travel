<section class="rounded-2xl bg-slate-50 p-5 sm:p-6">
    <h2 class="text-xl font-bold text-slate-900">
        Overall Satisfaction Score
    </h2>

    <p class="mt-2 text-sm text-slate-600">
        Click a star to rate your trip.
    </p>

    <div class="mt-4 flex flex-wrap items-center gap-4">
        <div class="flex gap-2" role="group" aria-label="Overall rating">
            @foreach (range(1, 5) as $star)
                <button
                    type="button"
                    data-overall-star="{{ $star }}"
                    class="text-4xl leading-none text-slate-300 transition hover:text-orange-400 focus:outline-none focus:ring-2 focus:ring-teal-600"
                    aria-label="Rate {{ $star }}"
                    aria-pressed="false"
                >
                    ★
                </button>
            @endforeach
        </div>

        <span
            id="overall-rating-label"
            class="rounded-full bg-orange-100 px-4 py-2 text-sm font-bold text-slate-800"
        >
            Not rated yet
        </span>
    </div>

    <input
        type="hidden"
        name="rating"
        id="overall-rating-value"
        value="{{ old('rating', '') }}"
    >

    @error('rating')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

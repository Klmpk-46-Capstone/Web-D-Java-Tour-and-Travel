<section>
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Foto Kenangan Perjalanan
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                Maksimal 5 foto, JPG, PNG, atau WebP, maksimal 5 MB per foto.
            </p>
        </div>

        <span id="photo-count" class="text-sm font-semibold text-teal-700">
            0 dari 5 foto
        </span>
    </div>

    <div class="mt-4">
        <label
            for="review-photos"
            class="flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-teal-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <path d="m21 15-5-5L5 21"/>
            </svg>

            <span class="mt-3 font-semibold text-slate-800">
                + Tambah Foto
            </span>

            <span class="mt-1 text-sm text-slate-500">
                JPG / PNG / WebP
            </span>
        </label>

        <input
            type="file"
            name="photos[]"
            id="review-photos"
            class="sr-only"
            accept="image/jpeg,image/png,image/webp"
            multiple
        >

        <div
            id="photo-preview"
            class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3"
        ></div>

        <p id="photo-error" class="mt-2 hidden text-sm text-red-600"></p>

        @error('photos')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @error('photos.*')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</section>

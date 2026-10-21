{{-- Image gallery --}}
<section aria-label="Foto paket wisata" class="grid grid-cols-1 gap-3 sm:grid-cols-5">
    <div class="overflow-hidden rounded-2xl bg-slate-200 sm:col-span-3">
        @if ($mainImage)
            <img
                src="{{ $mainImage }}"
                alt="Foto {{ $package->name }}"
                class="h-64 w-full object-cover sm:h-80 lg:h-96"
                fetchpriority="high"
            >
        @else
            <div class="flex h-64 items-center justify-center bg-slate-200 text-slate-500 sm:h-80 lg:h-96">
                Foto paket belum tersedia
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 sm:col-span-2 sm:grid-cols-1">
        <div class="overflow-hidden rounded-2xl bg-slate-200">
            @if ($mainImage)
                <img
                    src="{{ $mainImage }}"
                    alt="Pemandangan paket {{ $package->name }}"
                    class="h-32 w-full object-cover sm:h-44 lg:h-48"
                    loading="lazy"
                >
            @else
                <div class="flex h-32 items-center justify-center text-sm text-slate-500 sm:h-44">
                    Foto belum tersedia
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl bg-slate-200">
            @if ($mainImage)
                <img
                    src="{{ $mainImage }}"
                    alt="Destinasi paket {{ $package->name }}"
                    class="h-32 w-full object-cover sm:h-44 lg:h-48"
                    loading="lazy"
                >
            @else
                <div class="flex h-32 items-center justify-center text-sm text-slate-500 sm:h-44">
                    Foto belum tersedia
                </div>
            @endif
        </div>
    </div>
</section>

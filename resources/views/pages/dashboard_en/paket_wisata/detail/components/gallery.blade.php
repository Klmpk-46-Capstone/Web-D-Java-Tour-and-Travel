{{-- Image gallery --}}
<section aria-label="Tour package photos" class="grid grid-cols-1 gap-3 sm:grid-cols-5">
    <div class="overflow-hidden rounded-2xl bg-slate-200 sm:col-span-3">
        @if ($mainImage)
            <img
                src="{{ $mainImage }}"
                alt="Photo of {{ $package->name }}"
                class="h-64 w-full object-cover sm:h-80 lg:h-96"
                fetchpriority="high"
            >
        @else
            <div class="flex h-64 items-center justify-center bg-slate-200 text-slate-500 sm:h-80 lg:h-96">
                Package photo is not available yet
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 sm:col-span-2 sm:grid-cols-1">
        <div class="overflow-hidden rounded-2xl bg-slate-200">
            @if ($mainImage)
                <img
                    src="{{ $mainImage }}"
                    alt="Scenery of {{ $package->name }}"
                    class="h-32 w-full object-cover sm:h-44 lg:h-48"
                    loading="lazy"
                >
            @else
                <div class="flex h-32 items-center justify-center text-sm text-slate-500 sm:h-44">
                    Photo is not available yet
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-2xl bg-slate-200">
            @if ($mainImage)
                <img
                    src="{{ $mainImage }}"
                    alt="Destination of {{ $package->name }}"
                    class="h-32 w-full object-cover sm:h-44 lg:h-48"
                    loading="lazy"
                >
            @else
                <div class="flex h-32 items-center justify-center text-sm text-slate-500 sm:h-44">
                    Photo is not available yet
                </div>
            @endif
        </div>
    </div>
</section>

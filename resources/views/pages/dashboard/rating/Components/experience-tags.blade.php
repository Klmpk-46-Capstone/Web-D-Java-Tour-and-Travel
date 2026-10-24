<section>
    <h2 class="text-lg font-bold text-slate-900">
        Apa yang paling berkesan dari perjalanan Anda?
    </h2>

    <p class="mt-1 text-sm text-slate-600">
        Pilih semua yang sesuai dengan pengalaman Anda.
    </p>

    @php
        $experienceTags = [
            'driver_ramah' => 'Driver Ramah & Tepat Waktu',
            'armada_bersih' => 'Armada Bersih & AC Dingin',
            'rute_tepat' => 'Rute Perjalanan Tepat',
            'driver_berpengalaman' => 'Driver Berpengalaman',
            'cs_responsif' => 'Respons CS Cepat',
            'mobil_nyaman' => 'Mobil Nyaman',
        ];

        $selectedTags = old('experience_tags', []);
    @endphp

    <div class="mt-4 flex flex-wrap gap-3">
        @foreach ($experienceTags as $value => $label)
            <label class="cursor-pointer">
                <input
                    type="checkbox"
                    name="experience_tags[]"
                    value="{{ $value }}"
                    class="peer sr-only"
                    @checked(in_array($value, $selectedTags, true))
                >

                <span class="inline-flex rounded-full border border-slate-200 bg-blue-50 px-4 py-2 text-sm font-medium text-slate-700 transition peer-checked:border-teal-700 peer-checked:bg-teal-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-teal-600">
                    {{ $label }}
                </span>
            </label>
        @endforeach
    </div>

    @error('experience_tags')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</section>

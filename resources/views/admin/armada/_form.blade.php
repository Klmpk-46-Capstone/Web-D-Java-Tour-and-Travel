@php
    $model = $vehicle ?? null;
    $currentImage = $model?->image_url;
    $transmissionValue = old('transmission', $model?->transmission);
    $hasOldInput = session()->hasOldInput();
    $includesDriverChecked = $hasOldInput
        ? old('includes_driver')
        : ($model?->includes_driver ?? true);
    $isActiveChecked = $hasOldInput
        ? old('is_active')
        : ($model?->is_active ?? true);
@endphp

<form
    action="{{ $action }}"
    method="POST"
    enctype="multipart/form-data"
    class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-7"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- INFORMASI DASAR --}}
    <div>
        <h2 class="text-sm font-extrabold text-slate-900 mb-1 pb-2 border-b border-slate-100">Informasi Dasar Armada</h2>
        <p class="text-xs text-slate-500 mb-4">Data ini tampil sebagai identitas unit pada katalog dan halaman detail customer.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="vehicleName" class="block text-xs font-bold text-slate-700 mb-1">Nama Armada</label>
                <input
                    id="vehicleName"
                    type="text"
                    name="name"
                    value="{{ old('name', $model?->name) }}"
                    required
                    maxlength="150"
                    placeholder="Cth: Toyota Innova Zenix Hybrid"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600 @error('name') border-rose-400 @enderror"
                >
            </div>

            <div>
                <label for="vehicleCategory" class="block text-xs font-bold text-slate-700 mb-1">Kategori Armada</label>
                <select
                    id="vehicleCategory"
                    name="category"
                    required
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600 @error('category') border-rose-400 @enderror"
                >
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $model?->category ?? 'mpv') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="vehicleBadge" class="block text-xs font-bold text-slate-700 mb-1">Badge Promosi (Opsional)</label>
                <input
                    id="vehicleBadge"
                    type="text"
                    name="badge"
                    value="{{ old('badge', $model?->badge) }}"
                    maxlength="80"
                    placeholder="Cth: BEST DEAL / PREMIUM"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600"
                >
            </div>

            <div>
                <label for="vehicleSeats" class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Kursi</label>
                <input
                    id="vehicleSeats"
                    type="number"
                    name="seats"
                    value="{{ old('seats', $model?->seats ?? 7) }}"
                    required
                    min="1"
                    max="100"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600 @error('seats') border-rose-400 @enderror"
                >
            </div>

            <div>
                <label for="vehicleLuggage" class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Bagasi (Koper)</label>
                <input
                    id="vehicleLuggage"
                    type="number"
                    name="luggage_capacity"
                    value="{{ old('luggage_capacity', $model?->luggage_capacity ?? 3) }}"
                    required
                    min="0"
                    max="50"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600 @error('luggage_capacity') border-rose-400 @enderror"
                >
            </div>

            <div>
                <label for="vehicleTransmission" class="block text-xs font-bold text-slate-700 mb-1">Transmisi</label>
                <select
                    id="vehicleTransmission"
                    name="transmission"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600"
                >
                    @foreach ($transmissionOptions as $option)
                        <option value="{{ $option }}" {{ $transmissionValue === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="vehicleDescription" class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Armada</label>
                <textarea
                    id="vehicleDescription"
                    name="description"
                    rows="3"
                    placeholder="Jelaskan kenyamanan, fasilitas, dan keunggulan unit ini..."
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600"
                >{{ old('description', $model?->description) }}</textarea>
            </div>
        </div>
    </div>

    {{-- FOTO ARMADA --}}
    <div>
        <h2 class="text-sm font-extrabold text-slate-900 mb-1 pb-2 border-b border-slate-100">Foto Armada</h2>
        <p class="text-xs text-slate-500 mb-4">Upload foto utama dari perangkat. Format JPG, PNG, atau WebP, maksimal 2 MB. Rasio lanskap 16:9 disarankan.</p>

        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-start">
            <div class="w-full sm:w-64 shrink-0">
                <img
                    id="vehicleImagePreview"
                    src="{{ $currentImage ?? '' }}"
                    alt="Preview foto armada"
                    class="h-40 w-full rounded-xl border border-slate-200 object-cover {{ $currentImage ? '' : 'hidden' }}"
                >

                <div
                    id="vehicleImagePlaceholder"
                    class="h-40 w-full rounded-xl border border-dashed border-slate-300 bg-white grid place-items-center text-center px-4 {{ $currentImage ? 'hidden' : '' }}"
                >
                    <div>
                        <svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h14M6 17l-1-4 2-5h10l2 5-1 4M6 17v2m12-2v2M7 13h10"/></svg>
                        <p class="mt-2 text-[11px] text-slate-400">Belum ada foto armada</p>
                    </div>
                </div>
            </div>

            <div class="flex-1">
                <label for="vehicleImage" class="block text-xs font-bold text-slate-700 mb-1">Upload Foto dari Perangkat</label>
                <input
                    id="vehicleImage"
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-700 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-white hover:file:bg-teal-800 file:cursor-pointer"
                >
                <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
                    @if ($model)
                        Biarkan kosong jika tidak ingin mengganti foto saat ini.
                    @else
                        Foto bisa ditambahkan sekarang atau melalui menu edit setelah armada dibuat.
                    @endif
                </p>

                @if ($model?->image_path && \Illuminate\Support\Str::startsWith($model->image_path, ['http://', 'https://']))
                    <p class="mt-1 text-[11px] leading-relaxed text-amber-700">
                        Foto saat ini menggunakan URL eksternal. Upload foto baru untuk menyimpannya sebagai file di server.
                    </p>
                @endif

                @error('image')
                    <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- TARIF SEWA --}}
    <div>
        <h2 class="text-sm font-extrabold text-slate-900 mb-1 pb-2 border-b border-slate-100">Tarif Sewa</h2>
        <p class="text-xs text-slate-500 mb-4">Harga dalam Rupiah. Tarif dengan supir dan lepas kunci boleh dikosongkan jika belum tersedia.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="vehiclePricePerDay" class="block text-xs font-bold text-slate-700 mb-1">Tarif Dasar per Hari (IDR)</label>
                <input
                    id="vehiclePricePerDay"
                    type="number"
                    name="price_per_day"
                    value="{{ old('price_per_day', $model?->price_per_day ?? 0) }}"
                    required
                    min="0"
                    step="1000"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600 @error('price_per_day') border-rose-400 @enderror"
                >
            </div>

            <div>
                <label for="vehiclePriceWithDriver" class="block text-xs font-bold text-slate-700 mb-1">Tarif Dengan Supir (Opsional)</label>
                <input
                    id="vehiclePriceWithDriver"
                    type="number"
                    name="price_with_driver"
                    value="{{ old('price_with_driver', $model?->price_with_driver) }}"
                    min="0"
                    step="1000"
                    placeholder="Kosongkan jika tidak tersedia"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600"
                >
            </div>

            <div>
                <label for="vehiclePriceWithoutDriver" class="block text-xs font-bold text-slate-700 mb-1">Tarif Lepas Kunci (Opsional)</label>
                <input
                    id="vehiclePriceWithoutDriver"
                    type="number"
                    name="price_without_driver"
                    value="{{ old('price_without_driver', $model?->price_without_driver) }}"
                    min="0"
                    step="1000"
                    placeholder="Kosongkan jika tidak tersedia"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600"
                >
            </div>
        </div>

        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    name="includes_driver"
                    value="1"
                    {{ $includesDriverChecked ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500"
                >
                <div>
                    <span class="text-xs font-extrabold text-slate-800">Menyediakan Opsi Dengan Supir</span>
                    <span class="text-[11px] text-slate-500 block">Jika tarif dengan supir kosong, tarif dasar akan dipakai sebagai fallback.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ $isActiveChecked ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500"
                >
                <div>
                    <span class="text-xs font-extrabold text-slate-800">Status Aktif</span>
                    <span class="text-[11px] text-slate-500 block">Tampil dan dapat dipesan customer.</span>
                </div>
            </label>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-end gap-3">
        <a href="{{ $cancelUrl }}" class="px-5 py-2.5 text-xs font-bold text-slate-500 text-center hover:bg-slate-100 rounded-xl transition">
            Batal
        </a>
        <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 rounded-xl shadow-sm transition">
            {{ $submitLabel }}
        </button>
    </div>
</form>

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('vehicleImage');
        const preview = document.getElementById('vehicleImagePreview');
        const placeholder = document.getElementById('vehicleImagePlaceholder');

        if (!input || !preview || !placeholder) {
            return;
        }

        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', (event) => {
                preview.src = event.target?.result ?? '';
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            });

            reader.readAsDataURL(file);
        });
    })();
</script>
@endpush

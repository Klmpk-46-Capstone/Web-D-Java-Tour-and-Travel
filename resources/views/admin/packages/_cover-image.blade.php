@php
    $model = $package ?? null;
    $currentImage = $model?->image_url;
@endphp

<div class="md:col-span-2">
    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Sampul Paket</label>
    <p class="text-[11px] text-slate-500">
        Upload foto dari perangkat atau tempel URL foto. Salah satu saja sudah cukup, dan URL boleh dikosongkan.
    </p>

    <div class="mt-2 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-start">
        <div class="w-full sm:w-48 shrink-0">
            <img
                id="packageCoverPreview"
                src="{{ $currentImage ?? '' }}"
                alt="Preview foto sampul paket"
                class="h-28 w-full rounded-xl border border-slate-200 object-cover {{ $currentImage ? '' : 'hidden' }}"
            >

            <div
                id="packageCoverPlaceholder"
                class="h-28 w-full rounded-xl border border-dashed border-slate-300 bg-white grid place-items-center text-center px-4 {{ $currentImage ? 'hidden' : '' }}"
            >
                <div>
                    <svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <circle cx="8.5" cy="9" r="1.5"></circle>
                        <path d="m21 15-5-5L5 20"></path>
                    </svg>
                    <p class="mt-2 text-[11px] text-slate-400">Belum ada foto sampul</p>
                </div>
            </div>
        </div>

        <div class="flex-1 space-y-3">
            <div>
                <label for="packageCoverFile" class="block text-xs font-bold text-slate-700 mb-1">Upload Foto dari Perangkat</label>
                <input
                    id="packageCoverFile"
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-700 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-white hover:file:bg-teal-800 file:cursor-pointer"
                >
                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                    Format JPG, PNG, atau WebP. Maksimal 2 MB.
                    @if ($model)
                        Jika upload foto baru, foto saat ini akan diganti otomatis.
                    @endif
                </p>

                @error('image')
                    <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="packageCoverUrl" class="block text-xs font-bold text-slate-700 mb-1">URL Foto Sampul (Opsional)</label>
                <input
                    id="packageCoverUrl"
                    type="url"
                    name="image_url"
                    value="{{ old('image_url', $model?->image_url) }}"
                    placeholder="https://images.unsplash.com/..."
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600"
                >
                @error('image_url')
                    <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('packageCoverFile');
        const preview = document.getElementById('packageCoverPreview');
        const placeholder = document.getElementById('packageCoverPlaceholder');

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

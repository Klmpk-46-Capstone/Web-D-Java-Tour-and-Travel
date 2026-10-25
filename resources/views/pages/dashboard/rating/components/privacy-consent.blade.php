@php
    $isPublic = (bool) old('is_public', true);
    $identityVerified = (bool) old('identity_verified', true);
@endphp

<section class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5" aria-labelledby="privacy-consent-heading">
    <div class="flex items-center gap-2">
        <span class="grid h-8 w-8 place-items-center rounded-lg bg-white text-teal-700 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6l-7-3Z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </span>

        <h2 id="privacy-consent-heading" class="text-sm font-extrabold text-slate-900">
            Privasi &amp; Verifikasi
        </h2>
    </div>

    <div class="mt-4 space-y-3">
        <input type="hidden" name="is_public" value="0">

        <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3.5 transition hover:bg-teal-50/50">
            <input
                type="checkbox"
                name="is_public"
                value="1"
                class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                @checked($isPublic)
            >

            <span class="min-w-0">
                <span class="block text-sm font-bold text-slate-900">
                    Tampilkan ulasan ini secara publik
                </span>
                <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                    Ulasan dapat ditampilkan pada halaman paket atau armada untuk membantu calon pelanggan.
                </span>
            </span>
        </label>

        <input type="hidden" name="identity_verified" value="0">

        <label class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3.5 transition hover:bg-teal-50/50">
            <input
                type="checkbox"
                name="identity_verified"
                value="1"
                class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                @checked($identityVerified)
            >

            <span class="min-w-0">
                <span class="block text-sm font-bold text-slate-900">
                    Tampilkan identitas terverifikasi
                </span>
                <span class="mt-0.5 block text-xs leading-5 text-slate-500">
                    Ulasan terhubung dengan akun dan booking yang sudah lunas.
                </span>
            </span>
        </label>
    </div>

    @error('is_public')
        <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
    @enderror

    @error('identity_verified')
        <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
    @enderror
</section>

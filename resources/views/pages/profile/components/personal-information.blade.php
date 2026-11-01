<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">

    <div class="border-b border-slate-200 pb-5">

        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">
            Informasi Personal & Kontak
        </h2>

        <p class="mt-2 text-sm text-slate-500 sm:text-base">
            Kelola informasi pribadi untuk kebutuhan registrasi SIMAKSI TNBTS dan asuransi perjalanan resmi.
        </p>

    </div>

    <form
        action="{{ localized_route('profile.update') }}"
        method="POST"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf
        @method('PUT')

        {{-- Photo --}}
        <div class="mb-7 flex flex-wrap items-center gap-5">

            @if ($profilePhotoUrl)
                <img
                    src="{{ $profilePhotoUrl }}"
                    alt="Foto profil"
                    class="h-20 w-20 rounded-full object-cover"
                >
            @else
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-teal-100 text-3xl font-bold text-teal-800">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1">
                <label
                    for="profile_photo"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 16V4"/>
                        <path d="m7 9 5-5 5 5"/>
                        <path d="M20 16v4H4v-4"/>
                    </svg>

                    Ubah Foto Profil
                </label>

                <input
                    type="file"
                    name="profile_photo"
                    id="profile_photo"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="sr-only"
                >

                <p class="mt-2 text-xs text-slate-500">
                    JPG, PNG, atau WEBP. Maksimal 2 MB.
                </p>

                @error('profile_photo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            {{-- Full Name --}}
            <div>
                <label
                    for="name"
                    class="mb-2 block text-sm font-semibold text-slate-800"
                >
                    Nama Lengkap (KTP)
                    <span class="text-red-600">*</span>
                </label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <circle cx="9" cy="10" r="2"/>
                            <path d="M6 16c0-2 1-3 3-3s3 1 3 3"/>
                            <path d="M15 9h3M15 13h3"/>
                        </svg>
                    </span>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="100"
                        required
                        autocomplete="name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-base text-slate-800 outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                    >
                </div>

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- WhatsApp --}}
            <div>
                <label
                    for="whatsapp"
                    class="mb-2 block text-sm font-semibold text-slate-800"
                >
                    Nomor WhatsApp
                </label>

                <div class="relative">
                    {{-- Official WhatsApp Icon --}}
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600">
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true"
                        >
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.198-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.999-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.002-5.45 4.437-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.886 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.946L.057 24l6.305-1.654a11.882 11.882 0 005.684 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </span>

                    <input
                        type="tel"
                        name="whatsapp"
                        id="whatsapp"
                        value="{{ old('whatsapp', $user->whatsapp) }}"
                        maxlength="20"
                        required
                        autocomplete="tel"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-base text-slate-800 outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                    >
                </div>

                @error('whatsapp')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label
                        for="email"
                        class="text-sm font-semibold text-slate-800"
                    >
                        Email
                    </label>

                    @if ($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Terverifikasi
                        </span>
                    @endif
                </div>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>
                    </span>

                    <input
                        type="email"
                        id="email"
                        value="{{ $user->email }}"
                        readonly
                        class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 py-3 pl-12 pr-4 text-base text-slate-500 outline-none"
                    >
                </div>

                <p class="mt-1 text-xs text-slate-500">
                    Email digunakan sebagai identitas akun dan tidak dapat diubah langsung.
                </p>
            </div>

            {{-- Identity --}}
            <div>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label
                        for="identity_number"
                        class="text-sm font-semibold text-slate-800"
                    >
                        Nomor NIK / Paspor
                    </label>

                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="10" width="16" height="11" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>

                        Dilindungi
                    </span>
                </div>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v8M8 12h8"/>
                        </svg>
                    </span>

                    <input
                        type="text"
                        name="identity_number"
                        id="identity_number"
                        maxlength="50"
                        placeholder="{{ $maskedIdentity ? 'Tersimpan: ' . $maskedIdentity : 'Masukkan NIK atau nomor paspor' }}"
                        autocomplete="off"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-base text-slate-800 outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                    >
                </div>

                <p class="mt-1 text-xs text-slate-500">
                    Data identitas akan dienkripsi sebelum disimpan.
                    Kosongkan jika tidak ingin mengubahnya.
                </p>

                @error('identity_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Footer Form --}}
        <div class="mt-7 flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>

                Diperbarui:
                {{ $user->updated_at?->translatedFormat('d M Y, H:i') ?? '-' }}
            </div>

            <div class="flex flex-wrap items-center gap-3">

                <a
                    href="{{ localized_route('profile.index') }}"
                    class="rounded-xl px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-800 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-100"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/>
                        <path d="M17 21v-8H7v8M7 3v5h8"/>
                    </svg>

                    Simpan Perubahan
                </button>

            </div>

        </div>

    </form>

</div>

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">

    <div class="border-b border-slate-200 pb-5">
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">
            Keamanan & Sandi
        </h2>

        <p class="mt-2 text-sm text-slate-500">
            Kelola password dan lindungi akun East Java Trans kamu.
        </p>
    </div>

    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-start gap-3">
            <svg class="h-6 w-6 shrink-0 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>

            <div>
                <p class="font-semibold text-emerald-900">
                    Akun Dilindungi
                </p>

                <p class="mt-1 text-sm text-emerald-800">
                    Password disimpan menggunakan mekanisme hashing Laravel.
                </p>
            </div>
        </div>
    </div>

    <form
        action="{{ localized_route('profile.password.update') }}"
        method="POST"
        class="mt-7 space-y-5"
    >
        @csrf
        @method('PUT')

        <div>
            <label
                for="current_password"
                class="mb-2 block text-sm font-semibold text-slate-800"
            >
                Password Saat Ini
            </label>

            <input
                type="password"
                name="current_password"
                id="current_password"
                required
                autocomplete="current-password"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            @error('current_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="password"
                class="mb-2 block text-sm font-semibold text-slate-800"
            >
                Password Baru
            </label>

            <input
                type="password"
                name="password"
                id="password"
                required
                minlength="8"
                autocomplete="new-password"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            <p class="mt-2 text-xs text-slate-500">
                Minimal 8 karakter, terdiri dari huruf dan angka.
            </p>

            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label
                for="password_confirmation"
                class="mb-2 block text-sm font-semibold text-slate-800"
            >
                Konfirmasi Password Baru
            </label>

            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                required
                minlength="8"
                autocomplete="new-password"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >
        </div>

        <div class="flex justify-end border-t border-slate-200 pt-5">
            <button
                type="submit"
                class="rounded-xl bg-teal-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-100"
            >
                Perbarui Password
            </button>
        </div>

    </form>

</div>

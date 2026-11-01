<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">

    <div class="border-b border-slate-200 pb-5">
        <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">
            Notifikasi WhatsApp
        </h2>

        <p class="mt-2 text-sm text-slate-500">
            Atur preferensi penerimaan informasi perjalanan melalui WhatsApp.
        </p>
    </div>

    <div class="mt-6 flex items-center gap-4 rounded-xl border border-emerald-100 bg-emerald-50 p-5">

        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 11.5a8.5 8.5 0 0 1-12.8 7.3L3 20l1.2-4.7A8.5 8.5 0 1 1 21 11.5Z"/>
                <path d="M8 10c1 2 2 3 4 4"/>
            </svg>
        </div>

        <div class="min-w-0">
            <p class="font-semibold text-slate-900">
                {{ $user->whatsapp ?: 'Nomor WhatsApp belum ditambahkan' }}
            </p>

            <p class="mt-1 text-sm text-slate-600">
                Nomor WhatsApp yang terdaftar pada akun kamu.
            </p>
        </div>

    </div>

    <form
        action="{{ localized_route('profile.notifications.update') }}"
        method="POST"
        class="mt-7"
    >
        @csrf
        @method('PATCH')

        <div class="flex flex-col gap-5 rounded-xl border border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex-1">
                <h3 class="font-semibold text-slate-900">
                    Aktifkan Notifikasi WhatsApp
                </h3>

                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Izinkan East Java Trans menggunakan preferensi notifikasi WhatsApp untuk informasi pemesanan, jadwal perjalanan, dan pembaruan layanan.
                </p>
            </div>

            <label class="relative inline-flex cursor-pointer items-center">
                <input
                    type="hidden"
                    name="whatsapp_notifications_enabled"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="whatsapp_notifications_enabled"
                    value="1"
                    @checked($user->whatsapp_notifications_enabled)
                    class="peer sr-only"
                >

                <span class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-teal-700 peer-focus:ring-4 peer-focus:ring-teal-100"></span>

                <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
            </label>

        </div>

        <div class="mt-5 flex justify-end border-t border-slate-200 pt-5">
            <button
                type="submit"
                class="rounded-xl bg-teal-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-900"
            >
                Simpan Preferensi
            </button>
        </div>

    </form>

</div>

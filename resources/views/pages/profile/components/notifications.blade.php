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

    {{-- Daftar Riwayat & Notifikasi Aktif Customer --}}
    @php
        $userNotifs = \App\Services\CustomerNotificationService::forUser($user);
    @endphp

    <div class="mt-8 border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">
                    Pusat Notifikasi &amp; Aktivitas
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pemberitahuan terkini terkait perjalanan, status pesanan, dan pengajuan refund Anda.
                </p>
            </div>
            @if ($userNotifs->where('is_unread', true)->count() > 0)
                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">
                    {{ $userNotifs->where('is_unread', true)->count() }} Notifikasi Penting
                </span>
            @endif
        </div>

        <div class="space-y-3">
            @forelse ($userNotifs as $n)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border {{ $n['is_unread'] ? 'border-amber-200 bg-amber-50/50' : 'border-slate-200 bg-slate-50/50' }}">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $n['level'] === 'urgent' ? 'bg-amber-100 text-amber-800' : ($n['level'] === 'warning' ? 'bg-orange-100 text-orange-700' : 'bg-teal-100 text-teal-800') }}">
                            @if ($n['icon'] === 'calendar-clock')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif ($n['icon'] === 'credit-card')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-slate-900">{{ $n['title'] }}</h4>
                                <span class="text-[10px] text-slate-400">• {{ $n['date'] }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-600 leading-relaxed">{{ $n['message'] }}</p>
                        </div>
                    </div>
                    <div class="shrink-0 self-end sm:self-center">
                        <a href="{{ $n['url'] }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-800 hover:text-teal-900 bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-2xs hover:bg-slate-50 transition no-underline">
                            <span>Detail</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 bg-slate-50/50 rounded-xl border border-slate-100">
                    <svg class="mx-auto h-8 w-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="text-xs font-semibold text-slate-600">Tidak ada notifikasi aktif saat ini</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Semua pembaruan penting mengenai pesanan Anda akan dicatat di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

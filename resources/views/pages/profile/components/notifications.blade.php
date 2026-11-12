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
                @php
                    $isH1 = in_array($n['type'], ['h1_reminder', 'today_trip']);
                    $isPending = $n['type'] === 'payment_pending';
                    $isRefund = $n['type'] === 'refund_update';
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl border transition shadow-2xs {{ $isH1 ? 'border-amber-200/90 bg-gradient-to-r from-amber-50/80 via-white to-amber-50/30' : ($isPending ? 'border-orange-200/90 bg-gradient-to-r from-orange-50/80 via-white to-orange-50/30' : ($isRefund ? 'border-emerald-200/90 bg-gradient-to-r from-emerald-50/80 via-white to-emerald-50/30' : 'border-slate-200 bg-white')) }}">
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl font-bold shadow-2xs mt-0.5 {{ $isH1 ? 'bg-amber-100 text-amber-800' : ($isPending ? 'bg-orange-100 text-orange-700' : ($isRefund ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800')) }}">
                            @if ($n['icon'] === 'calendar-clock')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif ($n['icon'] === 'credit-card')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @elseif ($n['icon'] === 'flag')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-bold text-slate-900">{{ $n['title'] }}</h4>
                                @if (!empty($n['booking_code']))
                                    <span class="text-[10px] font-mono font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                                        #{{ $n['booking_code'] }}
                                    </span>
                                @endif
                                <span class="text-[11px] text-slate-400 font-medium">· {{ $n['date'] }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $n['message'] }}</p>
                        </div>
                    </div>
                    <div class="shrink-0 self-end sm:self-center">
                        <a href="{{ $n['url'] }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-teal-800 bg-white hover:bg-slate-50 border border-slate-200/90 px-3.5 py-2 rounded-xl shadow-xs transition no-underline">
                            <span>Lihat Detail</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 bg-slate-50/70 rounded-2xl border border-slate-100">
                    <div class="inline-grid h-12 w-12 place-items-center rounded-2xl bg-teal-50 text-teal-700 border border-teal-100 shadow-2xs mb-3">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Tidak ada notifikasi aktif saat ini</p>
                    <p class="text-xs text-slate-400 mt-1">Semua pembaruan jadwal trip, status transaksi, dan pengajuan tiket akan dicatat di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

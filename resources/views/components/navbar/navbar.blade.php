@php
    $isEnglish = app()->getLocale() === 'en';
@endphp

<header
    id="mainNavbar"
    data-is-english="{{ $isEnglish ? 'true' : 'false' }}"
    class="sticky top-0 z-50 bg-indigo-50 py-2 transition-all duration-300 md:py-3"
>
    <div class="container mx-auto px-4">
        <nav
            class="relative flex min-h-14 items-center justify-between gap-2 rounded-3xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition-all duration-300 md:min-h-16 md:gap-3 md:rounded-full md:px-5"
            aria-label="{{ $isEnglish ? 'Main navigation' : 'Navigasi utama' }}"
        >
            {{-- LOGO --}}
            <a
                href="{{ localized_route('home') }}"
                class="flex min-w-0 items-center gap-2 text-slate-900 no-underline md:shrink-0"
            >
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-teal-100 bg-teal-50 text-xl font-extrabold text-teal-700 md:h-10 md:w-10">
                    ✦
                </span>

                <span class="flex min-w-0 flex-col leading-tight">
                    <strong class="whitespace-nowrap text-xs tracking-tight sm:text-sm md:text-base">
                        D'Java Tour &amp; Travel
                    </strong>
                    <small class="mt-1 whitespace-nowrap text-xs font-extrabold tracking-wide text-teal-700">
                        EXPLORE EAST JAVA
                    </small>
                </span>
            </a>

            {{-- MENU UTAMA --}}
            <div
                id="navLinks"
                class="absolute left-0 right-0 top-full z-50 mt-2 hidden flex-col gap-1 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg lg:static lg:mt-0 lg:flex lg:w-auto lg:shrink-0 lg:flex-row lg:items-center lg:gap-1 lg:rounded-full lg:border-0 lg:bg-slate-50 lg:p-1 lg:shadow-none"
            >
                <a
                    data-nav-key="beranda"
                    href="{{ localized_route('home') }}"
                    class="{{ request()->routeIs('home', 'en.home') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors lg:whitespace-nowrap lg:px-3 lg:py-2.5"
                >
                    {{ $isEnglish ? 'Home' : 'Beranda' }}
                </a>

                <a
                    data-nav-key="armada"
                    href="{{ localized_route('armada') }}"
                    class="{{ request()->is('armada*', 'en/armada*') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors lg:whitespace-nowrap lg:px-3 lg:py-2.5"
                >
                    {{ $isEnglish ? 'Vehicles' : 'Armada' }}
                </a>

                <a
                    data-nav-key="paket"
                    href="{{ localized_route('packages.index') }}"
                    class="{{ request()->is('paket-wisata*', 'en/paket-wisata*') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors lg:whitespace-nowrap lg:px-3 lg:py-2.5"
                >
                    {{ $isEnglish ? 'Tour Packages' : 'Paket Wisata' }}
                </a>

                <a
                    data-nav-key="booking"
                    href="{{ localized_route('customer.bookings.index') }}"
                    class="{{ request()->is('riwayat-transaksi*', 'en/riwayat-transaksi*') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors lg:whitespace-nowrap lg:px-3 lg:py-2.5"
                >
                    {{ $isEnglish ? 'Transaction History' : 'Riwayat Transaksi' }}
                </a>

                {{-- PILIH BAHASA KHUSUS MOBILE --}}
                <div class="mt-2 border-t border-slate-200 pt-3 lg:hidden">
                    <p class="mb-2 px-1 text-xs font-semibold text-slate-500">
                        {{ $isEnglish ? 'Choose Language' : 'Pilih Bahasa' }}
                    </p>

                    @include('components.language-switcher')
                </div>

                {{-- WHATSAPP CS KHUSUS MOBILE --}}
                @if (!empty($whatsappUrl))
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-green-500 px-4 py-3 text-sm font-bold text-white no-underline transition hover:bg-green-600 lg:hidden"
                    >
                        WhatsApp Customer Service
                    </a>
                @else
                    <a
                        href="{{ localized_route('home') }}#kontak"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-green-500 px-4 py-3 text-sm font-bold text-white no-underline transition hover:bg-green-600 lg:hidden"
                    >
                        WhatsApp Customer Service
                    </a>
                @endif

                @auth
                    {{-- Dropdown profil mobile --}}
                    <div class="relative mt-2 lg:hidden">
                        <button
                            type="button"
                            data-profile-toggle
                            aria-expanded="false"
                            aria-controls="profileMenuMobile"
                            class="flex w-full items-center justify-between rounded-xl bg-teal-700 px-4 py-3 text-sm font-semibold text-white"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                                </svg>
                                {{ $isEnglish ? 'Profile' : 'Profil' }}
                            </span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div id="profileMenuMobile" class="mt-2 hidden rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
                            @include('components.partials.profile-menu', ['display' => 'mobile'])
                        </div>
                    </div>
                @else
                    <a
                        href="{{ localized_route('login') }}"
                        class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-teal-700 px-4 py-3 text-sm font-semibold text-white no-underline lg:hidden"
                    >
                        {{ $isEnglish ? 'Login / Sign In' : 'Login / Masuk' }}
                    </a>
                @endauth
            </div>

            {{-- AKSI DI SISI KANAN (Hanya Desktop) --}}
            <div class="hidden shrink-0 items-center gap-2 lg:flex">
                @include('components.language-switcher', ['compact' => true])

                @if (!empty($whatsappUrl))
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center whitespace-nowrap rounded-full bg-green-500 px-3 py-2 text-xs font-bold text-white no-underline transition hover:bg-green-600 md:px-4 md:py-3"
                    >
                        <svg class="mr-1 hidden md:block" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/>
                        </svg>
                        WhatsApp CS
                    </a>
                @else
                    <a
                        href="{{ localized_route('home') }}#kontak"
                        class="inline-flex items-center whitespace-nowrap rounded-full bg-green-500 px-3 py-2 text-xs font-bold text-white no-underline md:px-4 md:py-3"
                    >
                        WhatsApp CS
                    </a>
                @endif

                @auth
                    @php
                        $customerNotifs = \App\Services\CustomerNotificationService::forUser(auth()->user());
                        $unreadNotifCount = $customerNotifs->where('is_unread', true)->count();
                    @endphp

                    {{-- Tombol Notifikasi (Desktop) --}}
                    <div class="relative hidden lg:block">
                        <button
                            type="button"
                            data-profile-toggle
                            aria-expanded="false"
                            aria-controls="notifMenuDesktop"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200/90 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-teal-700 cursor-pointer shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            title="{{ $customerNotifs->count() }} {{ $isEnglish ? 'Notifications' : 'Notifikasi' }}"
                        >
                            <svg class="h-5 w-5 {{ $unreadNotifCount > 0 ? 'text-amber-600' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if ($unreadNotifCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[20px] h-5 flex items-center justify-center px-1 text-[10px] font-black bg-rose-500 text-white rounded-full ring-2 ring-white shadow-xs animate-bounce">
                                    {{ $unreadNotifCount }}
                                </span>
                            @elseif ($customerNotifs->count() > 0)
                                <span class="absolute top-0 right-0 h-2.5 w-2.5 rounded-full bg-teal-600 ring-2 ring-white"></span>
                            @endif
                        </button>

                        <div
                            id="notifMenuDesktop"
                            class="absolute right-0 top-full z-50 mt-3 hidden w-[360px] sm:w-[420px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl text-left transition-all duration-200"
                        >
                            {{-- Header --}}
                            <div class="px-4 py-3.5 bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-teal-500/20 text-teal-300 ring-1 ring-teal-400/30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-black tracking-wide uppercase text-white flex items-center gap-2 whitespace-nowrap">
                                            <span class="truncate">{{ $isEnglish ? 'Travel Notifications' : 'Notifikasi Perjalanan' }}</span>
                                            @if ($unreadNotifCount > 0)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-500 text-white shrink-0">
                                                    {{ $unreadNotifCount }} {{ $isEnglish ? 'New' : 'Baru' }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-300 font-medium truncate">{{ $isEnglish ? 'Updates & trip reminders' : 'Pembaruan & jadwal trip Anda' }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 whitespace-nowrap text-[10px] font-semibold text-slate-300 bg-white/10 px-2.5 py-1 rounded-full backdrop-blur-xs">
                                    {{ now()->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}
                                </span>
                            </div>

                            {{-- Scroll List --}}
                            <div class="max-h-[380px] overflow-y-auto p-3 space-y-2.5 divide-y divide-slate-100/70">
                                @forelse ($customerNotifs as $cn)
                                    @php
                                        $isH1 = in_array($cn['type'], ['h1_reminder', 'today_trip']);
                                        $isPending = $cn['type'] === 'payment_pending';
                                        $isRefund = $cn['type'] === 'refund_update';
                                    @endphp
                                    <a
                                        href="{{ $cn['url'] }}"
                                        class="block p-3 rounded-xl border transition-all duration-150 no-underline group {{ $isH1 ? 'border-amber-200/90 bg-gradient-to-r from-amber-50/80 via-white to-amber-50/30 hover:border-amber-400 hover:shadow-xs' : ($isPending ? 'border-orange-200/90 bg-gradient-to-r from-orange-50/80 via-white to-orange-50/30 hover:border-orange-400 hover:shadow-xs' : ($isRefund ? 'border-emerald-200/90 bg-gradient-to-r from-emerald-50/80 via-white to-emerald-50/30 hover:border-emerald-400 hover:shadow-xs' : 'border-slate-200/90 bg-white hover:bg-slate-50 hover:shadow-xs')) }}"
                                    >
                                        <div class="flex items-start gap-3">
                                            {{-- Icon Container --}}
                                            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl font-bold mt-0.5 shadow-2xs {{ $isH1 ? 'bg-amber-100 text-amber-800' : ($isPending ? 'bg-orange-100 text-orange-700' : ($isRefund ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800')) }}">
                                                @if ($cn['icon'] === 'calendar-clock')
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @elseif ($cn['icon'] === 'credit-card')
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                @elseif ($cn['icon'] === 'flag')
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </div>

                                            {{-- Content --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-1.5">
                                                    <h5 class="text-xs font-bold text-slate-900 group-hover:text-teal-800 truncate">
                                                        {{ $cn['title'] }}
                                                    </h5>
                                                    @if (!empty($cn['booking_code']))
                                                        <span class="shrink-0 text-[10px] font-mono font-bold text-slate-500 bg-white/90 border border-slate-200 px-1.5 py-0.2 rounded shadow-2xs">
                                                            #{{ $cn['booking_code'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <p class="mt-1 text-xs text-slate-600 leading-relaxed line-clamp-2">
                                                    {{ $cn['message'] }}
                                                </p>

                                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                                    <span class="text-[10px] font-medium text-slate-400 flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ $cn['date'] }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 font-bold text-xs {{ $isH1 ? 'text-amber-800' : ($isPending ? 'text-orange-700' : 'text-teal-700') }} group-hover:translate-x-0.5 transition-transform">
                                                        <span>{{ $isEnglish ? 'View Details' : 'Lihat Detail' }}</span>
                                                        <span>→</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="py-8 px-4 text-center">
                                        <div class="inline-grid h-12 w-12 place-items-center rounded-2xl bg-teal-50 text-teal-700 border border-teal-100 shadow-2xs mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                        </div>
                                        <h5 class="text-xs font-bold text-slate-900">
                                            {{ $isEnglish ? 'No notifications yet' : 'Belum Ada Notifikasi' }}
                                        </h5>
                                        <p class="text-[11px] text-slate-500 mt-1 max-w-[260px] mx-auto leading-relaxed">
                                            {{ $isEnglish ? 'Trip schedule reminders, payment alerts, and refund updates will appear here.' : 'Pengingat jadwal trip H-1, verifikasi pembayaran, dan status tiket akan otomatis muncul di sini.' }}
                                        </p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Footer --}}
                            <div class="px-4 py-2.5 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between">
                                <a
                                    href="{{ localized_route('customer.bookings.index') }}"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-700 hover:text-teal-900 no-underline transition"
                                >
                                    <span>{{ $isEnglish ? 'View All Bookings' : 'Semua Riwayat Pesanan' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a
                                    href="{{ localized_route('profile.index') }}"
                                    class="text-[10px] font-semibold text-slate-400 hover:text-slate-700 no-underline transition"
                                >
                                    {{ $isEnglish ? 'Settings' : 'Pengaturan' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Dropdown profil desktop --}}
                    <div class="relative hidden lg:block">
                        <button
                            type="button"
                            data-profile-toggle
                            aria-expanded="false"
                            aria-controls="profileMenuDesktop"
                            class="inline-flex items-center gap-2 whitespace-nowrap rounded-full bg-teal-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-teal-800"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                            </svg>
                            {{ $isEnglish ? 'Profile' : 'Profil' }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div id="profileMenuDesktop" class="absolute right-0 top-full z-50 mt-3 hidden w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 text-sm shadow-xl">
                            @include('components.partials.profile-menu', ['display' => 'desktop'])
                        </div>
                    </div>
                @else
                    <a
                        href="{{ localized_route('login') }}"
                        data-i18n="login"
                        class="hidden items-center whitespace-nowrap rounded-full bg-teal-700 px-4 py-2.5 text-xs font-bold text-white no-underline transition hover:bg-teal-800 lg:inline-flex"
                    >
                        {{ $isEnglish ? 'Login / Sign In' : 'Login / Masuk' }}
                    </a>
                @endauth
            </div>

            @auth
                {{-- Tombol Notifikasi Mobile --}}
                <div class="relative lg:hidden">
                    <button
                        type="button"
                        data-profile-toggle
                        aria-expanded="false"
                        aria-controls="notifMenuMobile"
                        class="relative grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200/90 bg-white text-slate-700 shadow-2xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                        aria-label="{{ $isEnglish ? 'Notifications' : 'Notifikasi' }}"
                    >
                        <svg class="h-4 w-4 {{ $unreadNotifCount > 0 ? 'text-amber-600' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if ($unreadNotifCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 h-3.5 w-3.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                        @endif
                    </button>

                    <div
                        id="notifMenuMobile"
                        class="absolute right-0 top-full z-50 mt-2 hidden w-[calc(100vw-2rem)] max-w-sm sm:w-[380px] overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl text-left transition-all duration-200"
                    >
                        {{-- Mobile Header --}}
                        <div class="px-3.5 py-3 bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 text-white flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-teal-500/20 text-teal-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </span>
                                <span class="text-xs font-black tracking-wide uppercase truncate">{{ $isEnglish ? 'Notifications' : 'Notifikasi' }}</span>
                            </div>
                            @if ($unreadNotifCount > 0)
                                <span class="shrink-0 rounded-full bg-rose-500 px-2 py-0.5 text-[9px] font-black text-white whitespace-nowrap">
                                    {{ $unreadNotifCount }} {{ $isEnglish ? 'New' : 'Baru' }}
                                </span>
                            @else
                                <span class="shrink-0 text-[10px] text-slate-300 font-medium whitespace-nowrap">{{ now()->timezone('Asia/Jakarta')->translatedFormat('d M') }}</span>
                            @endif
                        </div>

                        {{-- Mobile List --}}
                        <div class="max-h-72 overflow-y-auto p-2.5 space-y-2">
                            @forelse ($customerNotifs as $cn)
                                @php
                                    $isH1 = in_array($cn['type'], ['h1_reminder', 'today_trip']);
                                    $isPending = $cn['type'] === 'payment_pending';
                                    $isRefund = $cn['type'] === 'refund_update';
                                @endphp
                                <a
                                    href="{{ $cn['url'] }}"
                                    class="block p-2.5 rounded-xl border transition-all duration-150 no-underline group {{ $isH1 ? 'border-amber-200 bg-amber-50/70 hover:bg-amber-100/50' : ($isPending ? 'border-orange-200 bg-orange-50/70 hover:bg-orange-100/50' : ($isRefund ? 'border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/50' : 'border-slate-200 bg-white hover:bg-slate-50')) }}"
                                >
                                    <div class="flex items-start gap-2.5">
                                        <div class="grid h-7 w-7 shrink-0 place-items-center rounded-lg mt-0.5 text-xs font-bold {{ $isH1 ? 'bg-amber-200 text-amber-900' : ($isPending ? 'bg-orange-200 text-orange-900' : ($isRefund ? 'bg-emerald-200 text-emerald-900' : 'bg-teal-100 text-teal-800')) }}">
                                            @if ($cn['icon'] === 'calendar-clock')
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @elseif ($cn['icon'] === 'credit-card')
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-xs font-bold text-slate-900 truncate">{{ $cn['title'] }}</span>
                                                @if (!empty($cn['booking_code']))
                                                    <span class="text-[9px] font-mono font-bold text-slate-500 bg-white px-1 rounded border border-slate-200">#{{ $cn['booking_code'] }}</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5 leading-snug">{{ $cn['message'] }}</p>
                                            <div class="mt-1 flex items-center justify-between text-[10px]">
                                                <span class="text-slate-400">{{ $cn['date'] }}</span>
                                                <span class="font-bold {{ $isH1 ? 'text-amber-800' : ($isPending ? 'text-orange-700' : 'text-teal-700') }}">Detail →</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="py-6 px-3 text-center text-slate-400 text-xs">
                                    <svg class="mx-auto h-7 w-7 text-slate-300 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    <p class="font-semibold text-slate-600">{{ $isEnglish ? 'No notifications' : 'Belum ada notifikasi baru' }}</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Mobile Footer --}}
                        <div class="px-3.5 py-2 bg-slate-50 border-t border-slate-100 text-center">
                            <a
                                href="{{ localized_route('customer.bookings.index') }}"
                                class="text-xs font-bold text-teal-700 hover:text-teal-900 no-underline"
                            >
                                {{ $isEnglish ? 'View All Bookings →' : 'Lihat Semua Riwayat Pesanan →' }}
                            </a>
                        </div>
                    </div>
                </div>
            @endauth

            {{-- Tombol menu mobile (Hamburger) --}}
            <button
                id="menuToggle"
                type="button"
                aria-label="{{ $isEnglish ? 'Open navigation' : 'Buka navigasi' }}"
                aria-expanded="false"
                aria-controls="navLinks"
                class="grid h-9 w-9 shrink-0 place-items-center rounded-full border-0 bg-teal-700 text-white lg:hidden"
            >
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainNavbar = document.getElementById('mainNavbar');
    const isEnglish = mainNavbar?.dataset.isEnglish === 'true' || document.documentElement.lang === 'en';
    const mobileBreakpoint = 1024;
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    const navbarNav = mainNavbar?.querySelector('nav');

    // 1. Efek Kaca Blur saat Scroll
    function updateNavbarOnScroll() {
        if (!mainNavbar || !navbarNav) return;

        const isScrolled = window.scrollY > 20;

        // Header Background
        if (isScrolled) {
            mainNavbar.classList.remove('bg-indigo-50');
            mainNavbar.classList.add('bg-transparent');
        } else {
            mainNavbar.classList.remove('bg-transparent');
            mainNavbar.classList.add('bg-indigo-50');
        }

        // Nav Glassmorphism
        if (isScrolled) {
            navbarNav.classList.remove('bg-white', 'border-slate-200', 'shadow-sm');
            navbarNav.classList.add('bg-white/70', 'backdrop-blur-md', 'border-white/50', 'shadow-lg');
        } else {
            navbarNav.classList.remove('bg-white/70', 'backdrop-blur-md', 'border-white/50', 'shadow-lg');
            navbarNav.classList.add('bg-white', 'border-slate-200', 'shadow-sm');
        }
    }

    window.addEventListener('scroll', updateNavbarOnScroll, { passive: true });
    updateNavbarOnScroll();

    // 2. Mobile Menu Toggle Logic
    function setMobileMenu(open) {
        if (!menuToggle || !navLinks) return;

        navLinks.classList.toggle('hidden', !open);
        navLinks.classList.toggle('flex', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute('aria-label', open
            ? (isEnglish ? 'Close navigation' : 'Tutup navigasi')
            : (isEnglish ? 'Open navigation' : 'Buka navigasi'));
    }

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            const isOpen = menuToggle.getAttribute('aria-expanded') !== 'true';
            setMobileMenu(isOpen);
        });

        navLinks.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < mobileBreakpoint) {
                    setMobileMenu(false);
                }
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= mobileBreakpoint) {
                navLinks.classList.remove('hidden');
                navLinks.classList.add('flex');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', isEnglish ? 'Open navigation' : 'Buka navigasi');
            } else {
                setMobileMenu(false);
            }
        });
    }

    // 3. Profile Dropdown Logic
    const profileToggles = document.querySelectorAll('[data-profile-toggle]');

    function closeProfileMenus(exceptToggle) {
        profileToggles.forEach(function (toggle) {
            if (toggle === exceptToggle) return;

            toggle.setAttribute('aria-expanded', 'false');
            const menu = document.getElementById(toggle.getAttribute('aria-controls'));
            if (menu) menu.classList.add('hidden');
        });
    }

    profileToggles.forEach(function (toggle) {
        const menu = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!menu) return;

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();

            const shouldOpen = toggle.getAttribute('aria-expanded') !== 'true';
            closeProfileMenus(toggle);

            menu.classList.toggle('hidden', !shouldOpen);
            toggle.setAttribute('aria-expanded', String(shouldOpen));
        });

        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });
});
</script>

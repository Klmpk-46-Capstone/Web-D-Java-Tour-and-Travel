<header class="sticky top-0 z-50 bg-indigo-50 py-2 md:py-3">
    <div class="container mx-auto px-4">
        <nav
            class="relative flex min-h-14 items-center justify-between gap-2 rounded-3xl border border-slate-200 bg-white px-3 py-2 shadow-sm md:min-h-16 md:gap-3 md:rounded-full md:px-5"
            aria-label="Navigasi utama"
        >
            {{-- LOGO --}}
            <a
                href="{{ route('home') }}"
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
                class="absolute left-0 right-0 top-full z-50 mt-2 hidden flex-col gap-1 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg md:static md:mt-0 md:flex md:w-auto md:shrink-0 md:flex-row md:items-center md:gap-1 md:rounded-full md:border-0 md:bg-slate-50 md:p-1 md:shadow-none"
            >
                <a
                    data-nav-key="beranda"
                    href="{{ route('home') }}"
                    class="{{ request()->routeIs('home') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors md:whitespace-nowrap md:px-3 md:py-2.5"
                >
                    Beranda
                </a>

                <a
                    data-nav-key="armada"
                    href="{{ url('/armada') }}"
                    class="{{ request()->is('armada*') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors md:whitespace-nowrap md:px-3 md:py-2.5"
                >
                    Armada
                </a>

                <a
                    data-nav-key="paket"
                    href="{{ url('/paket-wisata') }}"
                    class="{{ request()->is('paket-wisata*') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors md:whitespace-nowrap md:px-3 md:py-2.5"
                >
                    Paket Wisata
                </a>

                <a
                    data-nav-key="booking"
                    href="{{ url('/cara-booking') }}"
                    class="{{ request()->is('cara-booking*') ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-teal-700 hover:text-white' }} block rounded-full px-4 py-3 text-sm font-semibold no-underline transition-colors md:whitespace-nowrap md:px-3 md:py-2.5"
                >
                    Cara Booking
                </a>

                @auth
                    {{-- Dropdown profil mobile --}}
                    <div class="relative md:hidden">
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
                                Profile
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
                        href="{{ route('login') }}"
                        class="rounded-full bg-teal-700 px-4 py-3 text-sm font-semibold text-white no-underline md:hidden"
                    >
                        Login / Masuk
                    </a>
                @endauth
            </div>

            {{-- AKSI DI SISI KANAN --}}
            <div class="flex shrink-0 items-center gap-2">
                <div class="flex items-center gap-1" aria-label="Pilih bahasa">
                    <button
                        type="button"
                        data-lang="id"
                        aria-pressed="true"
                        class="rounded-full border border-teal-100 bg-teal-50 px-2 py-1 text-xs font-semibold text-teal-700"
                    >
                        ID
                    </button>
                    <button
                        type="button"
                        data-lang="en"
                        aria-pressed="false"
                        class="rounded-full border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-teal-50 hover:text-teal-700"
                    >
                        EN
                    </button>
                </div>

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
                        href="{{ route('home') }}#kontak"
                        class="inline-flex items-center whitespace-nowrap rounded-full bg-green-500 px-3 py-2 text-xs font-bold text-white no-underline md:px-4 md:py-3"
                    >
                        WhatsApp CS
                    </a>
                @endif

                @auth
                    {{-- Dropdown profil desktop --}}
                    <div class="relative hidden md:block">
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
                            Profile
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
                        href="{{ route('login') }}"
                        data-i18n="login"
                        class="hidden items-center whitespace-nowrap rounded-full bg-teal-700 px-4 py-2.5 text-xs font-bold text-white no-underline transition hover:bg-teal-800 md:inline-flex"
                    >
                        Login / Masuk
                    </a>
                @endauth

                {{-- Tombol menu mobile --}}
                <button
                    id="menuToggle"
                    type="button"
                    aria-label="Buka navigasi"
                    aria-expanded="false"
                    aria-controls="navLinks"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full border-0 bg-teal-700 text-white md:hidden"
                >
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mobileBreakpoint = 768;
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    function setMobileMenu(open) {
        if (!menuToggle || !navLinks) return;

        navLinks.classList.toggle('hidden', !open);
        navLinks.classList.toggle('flex', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
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
                menuToggle.setAttribute('aria-label', 'Buka navigasi');
            } else {
                setMobileMenu(false);
            }
        });
    }

    const translations = {
        id: {
            beranda: 'Beranda',
            armada: 'Armada',
            paket: 'Paket Wisata',
            booking: 'Cara Booking',
            login: 'Login / Masuk'
        },
        en: {
            beranda: 'Home',
            armada: 'Vehicles',
            paket: 'Tour Packages',
            booking: 'How to Book',
            login: 'Login / Sign In'
        }
    };

    document.querySelectorAll('[data-lang]').forEach(function (button) {
        button.addEventListener('click', function () {
            const language = button.dataset.lang;
            const dictionary = translations[language];

            if (!dictionary) return;

            document.documentElement.lang = language;

            document.querySelectorAll('[data-lang]').forEach(function (languageButton) {
                const selected = languageButton === button;

                languageButton.classList.toggle('border-teal-100', selected);
                languageButton.classList.toggle('bg-teal-50', selected);
                languageButton.classList.toggle('text-teal-700', selected);
                languageButton.classList.toggle('border-slate-200', !selected);
                languageButton.classList.toggle('bg-white', !selected);
                languageButton.classList.toggle('text-slate-500', !selected);
                languageButton.setAttribute('aria-pressed', String(selected));
            });

            document.querySelectorAll('[data-nav-key]').forEach(function (link) {
                const key = link.dataset.navKey;
                if (dictionary[key]) link.textContent = dictionary[key];
            });

            document.querySelectorAll('[data-i18n]').forEach(function (element) {
                const key = element.dataset.i18n;
                if (dictionary[key]) element.textContent = dictionary[key];
            });
        });
    });

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

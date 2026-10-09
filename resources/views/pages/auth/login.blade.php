
@extends('layouts.auth')

@section('title', 'Masuk')

@section('hero-title')
    Jelajahi<br class="hidden sm:block">
    dengan Lebih Leluasa
@endsection

@section('hero-description')
    Layanan rental mobil berkelas dan tur privat terpercaya di Bromo, Ijen, Malang, dan Surabaya.
@endsection

@section('content')
    {{-- Tombol Kembali ke Beranda --}}
    <a
        href="{{ route('home') }}"
        class="mb-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 no-underline shadow-sm transition duration-200 hover:border-teal-200 hover:bg-teal-50 hover:text-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200"
        aria-label="Kembali ke Beranda"
    >
        <svg
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M19 12H5"/>
            <path d="m12 19-7-7 7-7"/>
        </svg>

        <span>Kembali ke Beranda</span>
    </a>

    {{-- Judul Login --}}
    <div>
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
            Selamat Datang Kembali
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-slate-600 sm:text-base">
            Masuk ke akun Anda untuk mengelola pemesanan armada dan paket wisata.
        </p>
    </div>

    {{-- Login dengan Google --}}
    <button
        type="button"
        disabled
        title="Login Google perlu dikonfigurasi terlebih dahulu"
        class="mt-5 flex w-full cursor-not-allowed items-center justify-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm opacity-80 sm:text-base"
    >
        <span class="text-lg font-extrabold text-blue-600" aria-hidden="true">
            G
        </span>

        Masuk dengan Google
    </button>

    {{-- Pemisah --}}
    <div class="my-5 flex items-center gap-3">
        <span class="h-px flex-1 bg-slate-200"></span>

        <span class="text-xs font-semibold text-slate-500 sm:text-sm">
            atau masuk dengan email
        </span>

        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    {{-- Form Login --}}
    <form
        action="{{ route('login.store') }}"
        method="POST"
        class="space-y-4"
    >
        @csrf

        {{-- Email atau Nomor WhatsApp --}}
        <div>
            <label
                for="login"
                class="mb-1.5 block text-sm font-semibold text-slate-800 sm:text-base"
            >
                Email atau No. WhatsApp
            </label>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-indigo-50 px-3 transition focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-100 sm:px-4">
                <svg
                    class="h-5 w-5 shrink-0 text-slate-600 sm:h-6 sm:w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="m3 7 9 6 9-6"/>
                </svg>

                <input
                    id="login"
                    name="login"
                    type="text"
                    value="{{ old('login') }}"
                    placeholder="nama@email.com atau 081234567890"
                    autocomplete="username"
                    required
                    maxlength="255"
                    class="min-w-0 w-full border-0 bg-transparent py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0 sm:text-base"
                >
            </div>

            @error('login')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Kata Sandi --}}
        <div>
            <label
                for="password"
                class="mb-1.5 block text-sm font-semibold text-slate-800 sm:text-base"
            >
                Kata Sandi
            </label>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-indigo-50 px-3 transition focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-100 sm:px-4">
                <svg
                    class="h-5 w-5 shrink-0 text-slate-600 sm:h-6 sm:w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <rect x="5" y="10" width="14" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    <path d="M12 14v3"/>
                </svg>

                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Masukkan kata sandi akun"
                    autocomplete="current-password"
                    required
                    class="min-w-0 w-full border-0 bg-transparent py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0 sm:text-base"
                >

                <button
                    type="button"
                    data-toggle-password="password"
                    aria-label="Tampilkan kata sandi"
                    class="shrink-0 rounded-lg p-1 text-slate-600 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-600"
                >
                    <svg
                        class="h-5 w-5 sm:h-6 sm:w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            @error('password')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Ingat Saya dan Lupa Kata Sandi --}}
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
            <label class="flex cursor-pointer items-center gap-2 text-slate-600">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                >

                Ingat saya
            </label>

            <a
                href="mailto:admin@djavatravel.com"
                class="font-semibold text-teal-700 transition hover:text-teal-900"
            >
                Lupa kata sandi?
            </a>
        </div>

        {{-- Tombol Masuk --}}
        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-200"
        >
            Masuk ke Akun
            <span aria-hidden="true">→</span>
        </button>
    </form>

    {{-- Navigasi ke Register --}}
    <p class="mt-5 text-center text-sm text-slate-600 sm:text-base">
        Belum punya akun?

        <a
            href="{{ route('register') }}"
            class="font-bold text-teal-700 transition hover:text-teal-900"
        >
            Daftar Sekarang
        </a>
    </p>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(
                    button.dataset.togglePassword
                );

                if (!input) {
                    return;
                }

                const isPassword = input.type === 'password';

                input.type = isPassword ? 'text' : 'password';

                button.setAttribute(
                    'aria-label',
                    isPassword
                        ? 'Sembunyikan kata sandi'
                        : 'Tampilkan kata sandi'
                );
            });
        });
    </script>
@endpush

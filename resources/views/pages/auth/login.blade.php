@extends('layouts.auth')

@section('title', app()->getLocale() === 'en' ? 'Login' : 'Masuk')

@section('content')
    @php
        $isEnglish = app()->getLocale() === 'en';
    @endphp

    {{-- Judul --}}
    <div class="text-center">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
            {{ $isEnglish ? 'Sign In to Your Account' : 'Masuk ke Akun' }}
        </h1>

        <p class="mt-2 text-base leading-relaxed text-slate-600">
            {{ $isEnglish
                ? 'Access your rental bookings and tour package reservations.'
                : 'Akses pesanan rental dan reservasi paket wisata Anda.' }}
        </p>
    </div>

    {{-- Login Google --}}
    <a
        href="{{ localized_route('google.redirect') }}"
        class="mt-6 flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
    >
        <svg
            class="h-5 w-5"
            viewBox="0 0 48 48"
            aria-hidden="true"
        >
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.73 7.18l7.64 5.93c4.46-4.13 7.13-10.22 7.13-17.58Z"/>
            <path fill="#FBBC05" d="M10.53 28.59a14.4 14.4 0 0 1 0-9.18l-7.98-6.19a24 24 0 0 0 0 21.56l7.98-6.19Z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.87l-7.65-5.93c-2.12 1.42-4.84 2.26-8.25 2.26-6.26 0-11.57-4.22-13.46-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
        </svg>

        {{ $isEnglish ? 'Continue with Google' : 'Masuk dengan Google' }}
    </a>

    @error('google')
        <p class="mt-3 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

    {{-- Pemisah --}}
    <div class="my-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-slate-200"></span>

        <span class="whitespace-nowrap text-sm font-medium text-slate-600">
            {{ $isEnglish ? 'or sign in with email' : 'atau masuk dengan email' }}
        </span>

        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    {{-- Form Login --}}
    <form
        action="{{ route('login.store') }}"
        method="POST"
        class="space-y-5"
    >
        @csrf

        {{-- Email atau WhatsApp --}}
        <div>
            <label
                for="login"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                {{ $isEnglish ? 'Email or WhatsApp Number' : 'Email atau No. WhatsApp' }}
            </label>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-100">
                <svg
                    class="h-5 w-5 shrink-0 text-slate-700"
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
                    placeholder="{{ $isEnglish
                        ? 'name@email.com or 081234567890'
                        : 'nama@email.com atau 081234567890' }}"
                    autocomplete="username"
                    required
                    maxlength="255"
                    class="min-w-0 w-full border-0 bg-transparent py-3.5 text-base text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0"
                >
            </div>

            @error('login')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Kata Sandi --}}
        <div>
            <label
                for="password"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                {{ $isEnglish ? 'Password' : 'Kata Sandi' }}
            </label>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 transition focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-100">
                <svg
                    class="h-5 w-5 shrink-0 text-slate-700"
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
                    placeholder="{{ $isEnglish
                        ? 'Enter your account password'
                        : 'Masukkan kata sandi akun' }}"
                    autocomplete="current-password"
                    required
                    class="min-w-0 w-full border-0 bg-transparent py-3.5 text-base text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0"
                >

                <button
                    type="button"
                    data-toggle-password="password"
                    data-show-password="{{ $isEnglish ? 'Show password' : 'Tampilkan kata sandi' }}"
                    data-hide-password="{{ $isEnglish ? 'Hide password' : 'Sembunyikan kata sandi' }}"
                    aria-label="{{ $isEnglish ? 'Show password' : 'Tampilkan kata sandi' }}"
                    aria-pressed="false"
                    class="shrink-0 rounded-lg p-1 text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-600"
                >
                    <svg
                        class="h-5 w-5"
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
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Ingat Saya dan Lupa Password --}}
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm sm:text-base">
            <label class="flex cursor-pointer items-center gap-2 text-slate-700">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                >

                {{ $isEnglish ? 'Remember me' : 'Ingat saya' }}
            </label>

            <a
                href="{{ localized_route('password.request') }}"
                class="font-semibold text-teal-700 transition hover:text-teal-900"
            >
                {{ $isEnglish ? 'Forgot password?' : 'Lupa kata sandi?' }}
            </a>
        </div>

        {{-- Tombol Masuk --}}
        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-200"
        >
            {{ $isEnglish ? 'Sign In Now' : 'Masuk Sekarang' }}
        </button>
    </form>

    {{-- Navigasi Register --}}
    <p class="mt-6 text-center text-base text-slate-600">
        {{ $isEnglish ? 'Don’t have an account?' : 'Belum punya akun?' }}

        <a
            href="{{ route('register') }}"
            class="font-bold text-teal-700 transition hover:text-teal-900"
        >
            {{ $isEnglish ? 'Create an Account' : 'Daftar Sekarang' }}
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
                        ? button.dataset.hidePassword
                        : button.dataset.showPassword
                );

                button.setAttribute(
                    'aria-pressed',
                    String(isPassword)
                );
            });
        });
    </script>
@endpush

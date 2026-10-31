@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
    {{-- Judul --}}
    <div class="text-center">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
            Buat Akun Baru
        </h1>

        <p class="mt-2 text-base leading-relaxed text-slate-600">
            Mulai eksplorasi terbaik Anda bersama kami hari ini.
        </p>
    </div>

    {{-- Register Google --}}
    <button
        type="button"
        disabled
        title="Pendaftaran Google perlu dikonfigurasi terlebih dahulu"
        class="mt-6 flex w-full cursor-not-allowed items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-semibold text-slate-700 opacity-80"
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

        Daftar dengan Google
    </button>

    {{-- Pemisah --}}
    <div class="my-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-slate-200"></span>

        <span class="whitespace-nowrap text-xs font-semibold uppercase tracking-wide text-slate-500">
            Atau daftar dengan email
        </span>

        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    {{-- Form Register --}}
    <form
        action="{{ route('register.store') }}"
        method="POST"
        class="space-y-4"
    >
        @csrf

        {{-- Nama Lengkap --}}
        <div>
            <label
                for="name"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                Nama Lengkap
            </label>

            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                placeholder="Nama lengkap Anda"
                autocomplete="name"
                required
                maxlength="100"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            @error('name')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Nomor WhatsApp --}}
        <div>
            <label
                for="whatsapp"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                Nomor WhatsApp
            </label>

            <input
                id="whatsapp"
                name="whatsapp"
                type="tel"
                value="{{ old('whatsapp') }}"
                placeholder="081234567890"
                autocomplete="tel"
                inputmode="tel"
                required
                maxlength="20"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            @error('whatsapp')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label
                for="email"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                Email
            </label>

            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                autocomplete="email"
                required
                maxlength="255"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            @error('email')
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
                Kata Sandi
            </label>

            <input
                id="password"
                name="password"
                type="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                required
                minlength="8"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            <p class="mt-2 text-xs text-slate-500">
                Minimal 8 karakter, dengan huruf dan angka.
            </p>

            @error('password')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Konfirmasi Kata Sandi --}}
        <div>
            <label
                for="password_confirmation"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                Konfirmasi Kata Sandi
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                placeholder="Ulangi kata sandi"
                autocomplete="new-password"
                required
                minlength="8"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >
        </div>

        {{-- Persetujuan --}}
        <label class="flex cursor-pointer items-start gap-2 pt-1 text-sm leading-relaxed text-slate-600">

            <input
                id="terms"
                name="terms"
                type="checkbox"
                value="1"
                required
                class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
            >

            <span>
                Saya menyetujui

                <a
                    href="{{ route('home') }}"
                    class="font-semibold text-teal-700 transition hover:text-teal-900"
                >
                    Syarat &amp; Ketentuan
                </a>
            </span>

        </label>

        {{-- Tombol Daftar --}}
        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-700 px-5 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-200"
        >
            Daftar Sekarang

            <span aria-hidden="true">
                →
            </span>
        </button>

    </form>

    {{-- Navigasi Login --}}
    <p class="mt-6 text-center text-base text-slate-600">
        Sudah punya akun?

        <a
            href="{{ route('login') }}"
            class="font-bold text-teal-700 transition hover:text-teal-900"
        >
            Masuk di sini
        </a>
    </p>

@endsection


@extends('layouts.auth')

@section('title', 'Daftar')

@section('hero-title')
    Satu Akun untuk<br class="hidden sm:block">
    Semua Perjalanan Anda
@endsection

@section('hero-description')
    Mulai eksplorasi terbaik Anda bersama kami hari ini.
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

    {{-- Judul Register --}}
    <div>
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
            Buat Akun
        </h2>

        <p class="mt-2 text-sm leading-relaxed text-slate-600 sm:text-base">
            Mulai eksplorasi terbaik Anda bersama kami hari ini.
        </p>
    </div>

    {{-- Register dengan Google --}}
    <button
        type="button"
        disabled
        title="Pendaftaran Google perlu dikonfigurasi terlebih dahulu"
        class="mt-5 flex w-full cursor-not-allowed items-center justify-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm opacity-80 sm:text-base"
    >
        <span class="text-lg font-extrabold text-blue-600" aria-hidden="true">
            G
        </span>

        Daftar dengan Google
    </button>

    {{-- Pemisah --}}
    <div class="my-5 flex items-center gap-3">
        <span class="h-px flex-1 bg-slate-200"></span>

        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">
            Atau daftar dengan email
        </span>

        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    {{-- Form Register --}}
    <form
        action="{{ route('register.store') }}"
        method="POST"
        class="space-y-3"
    >
        @csrf

        {{-- Nama Lengkap --}}
        <div>
            <label
                for="name"
                class="mb-1 block text-sm font-semibold text-slate-800"
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
                class="w-full rounded-lg border border-transparent bg-indigo-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-teal-600 focus:ring-2 focus:ring-teal-100 sm:text-base"
            >

            @error('name')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Nomor WhatsApp dan Email --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label
                    for="whatsapp"
                    class="mb-1 block text-sm font-semibold text-slate-800"
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
                    class="w-full rounded-lg border border-transparent bg-indigo-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-teal-600 focus:ring-2 focus:ring-teal-100 sm:text-base"
                >

                @error('whatsapp')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="email"
                    class="mb-1 block text-sm font-semibold text-slate-800"
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
                    class="w-full rounded-lg border border-transparent bg-indigo-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-teal-600 focus:ring-2 focus:ring-teal-100 sm:text-base"
                >

                @error('email')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- Kata Sandi --}}
        <div>
            <label
                for="password"
                class="mb-1 block text-sm font-semibold text-slate-800"
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
                class="w-full rounded-lg border border-transparent bg-indigo-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-teal-600 focus:ring-2 focus:ring-teal-100 sm:text-base"
            >

            <p class="mt-1 text-xs text-slate-500">
                Minimal 8 karakter, dengan huruf dan angka.
            </p>

            @error('password')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Konfirmasi Kata Sandi --}}
        <div>
            <label
                for="password_confirmation"
                class="mb-1 block text-sm font-semibold text-slate-800"
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
                class="w-full rounded-lg border border-transparent bg-indigo-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-500 focus:border-teal-600 focus:ring-2 focus:ring-teal-100 sm:text-base"
            >
        </div>

        {{-- Persetujuan Syarat dan Ketentuan --}}
        <label class="flex cursor-pointer items-start gap-2 pt-1 text-sm leading-relaxed text-slate-600">
            <input
                id="terms"
                name="terms"
                type="checkbox"
                value="1"
                required
                class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
            >

            <span>
                Saya menyetujui
                <a
                    href="{{ route('home') }}"
                    class="font-semibold text-teal-700 hover:text-teal-900"
                >
                    Syarat &amp; Ketentuan
                </a>
            </span>
        </label>

        {{-- Tombol Daftar --}}
        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-3.5 text-base font-bold text-white transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-200"
        >
            Daftar Sekarang
            <span aria-hidden="true">→</span>
        </button>
    </form>

    {{-- Navigasi ke Login --}}
    <p class="mt-5 text-center text-sm text-slate-600 sm:text-base">
        Sudah punya akun?

        <a
            href="{{ route('login') }}"
            class="font-bold text-teal-700 transition hover:text-teal-900"
        >
            Masuk di sini
        </a>
    </p>
@endsection

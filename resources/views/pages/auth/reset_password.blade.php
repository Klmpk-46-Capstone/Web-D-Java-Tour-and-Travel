@extends('layouts.auth')

@section('title', app()->getLocale() === 'en' ? 'Reset Password' : 'Atur Ulang Password')

@section('content')
    @php
        $isEnglish = app()->getLocale() === 'en';
    @endphp

    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-50 text-teal-700">
            <svg
                class="h-8 w-8"
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
        </div>

        <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
            {{ $isEnglish ? 'Create New Password' : 'Buat Password Baru' }}
        </h1>

        <p class="mt-3 text-base leading-relaxed text-slate-600">
            {{ $isEnglish
                ? 'Create a new password to secure your account.'
                : 'Buat kata sandi baru untuk mengamankan akun Anda.' }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ localized_route('password.update') }}"
        method="POST"
        class="mt-8 space-y-5"
    >
        @csrf

        <input
            type="hidden"
            name="token"
            value="{{ $token }}"
        >

        <div>
            <label
                for="email"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                {{ $isEnglish ? 'Email Address' : 'Alamat Email' }}
            </label>

            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $email) }}"
                placeholder="nama@email.com"
                autocomplete="email"
                maxlength="255"
                required
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >
        </div>

        <div>
            <label
                for="password"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                {{ $isEnglish ? 'New Password' : 'Kata Sandi Baru' }}
            </label>

            <input
                id="password"
                name="password"
                type="password"
                placeholder="{{ $isEnglish ? 'At least 8 characters' : 'Minimal 8 karakter' }}"
                autocomplete="new-password"
                minlength="8"
                required
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >

            <p class="mt-2 text-xs text-slate-500">
                {{ $isEnglish
                    ? 'At least 8 characters, including letters and numbers.'
                    : 'Minimal 8 karakter, dengan huruf dan angka.' }}
            </p>
        </div>

        <div>
            <label
                for="password_confirmation"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                {{ $isEnglish ? 'Confirm New Password' : 'Konfirmasi Kata Sandi Baru' }}
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                placeholder="{{ $isEnglish ? 'Re-enter your new password' : 'Ulangi kata sandi baru' }}"
                autocomplete="new-password"
                minlength="8"
                required
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            >
        </div>

        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-200"
        >
            {{ $isEnglish ? 'Reset Password' : 'Atur Ulang Password' }}
        </button>
    </form>

    <p class="mt-6 text-center text-base text-slate-600">
        {{ $isEnglish ? 'Remember your password?' : 'Ingat kata sandi Anda?' }}

        <a
            href="{{ localized_route('login') }}"
            class="font-bold text-teal-700 transition hover:text-teal-900"
        >
            {{ $isEnglish ? 'Back to Sign In' : 'Kembali ke Login' }}
        </a>
    </p>
@endsection

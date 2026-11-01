@extends('layouts.auth')

@section('title', app()->getLocale() === 'en' ? 'Forgot Password' : 'Lupa Password')

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
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <path d="m3 7 9 6 9-6"/>
                <path d="M16 3v4"/>
                <path d="M14 5h4"/>
            </svg>
        </div>

        <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
            {{ $isEnglish ? 'Forgot Password?' : 'Lupa Password?' }}
        </h1>

        <p class="mt-3 text-base leading-relaxed text-slate-600">
            {{ $isEnglish
                ? 'Enter your registered email address. We will send you a link to reset your password.'
                : 'Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.' }}
        </p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm leading-relaxed text-teal-800">
            {{ session('status') }}
        </div>
    @endif

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
        action="{{ localized_route('password.email') }}"
        method="POST"
        class="mt-8 space-y-5"
    >
        @csrf

        <div>
            <label
                for="email"
                class="mb-2 block text-sm font-semibold text-slate-900 sm:text-base"
            >
                {{ $isEnglish ? 'Email Address' : 'Alamat Email' }}
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
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="{{ $isEnglish ? 'name@email.com' : 'nama@email.com' }}"
                    autocomplete="email"
                    maxlength="255"
                    required
                    autofocus
                    class="min-w-0 w-full border-0 bg-transparent py-3.5 text-base text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0"
                >
            </div>

            @error('email')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-800 px-5 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-teal-900 focus:outline-none focus:ring-4 focus:ring-teal-200"
        >
            {{ $isEnglish ? 'Send Reset Link' : 'Kirim Tautan Reset' }}
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

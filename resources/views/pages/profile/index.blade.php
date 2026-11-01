<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Profil Saya - East Java Trans</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    @include('components.navbar.navbar')

    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="mb-4 flex items-center gap-2 text-sm font-medium text-slate-500">
            <a
                href="{{ localized_route('home') }}"
                class="transition hover:text-teal-700"
            >
                Beranda
            </a>

            <span>/</span>

            <span class="text-slate-800">
                Profil & Pengaturan
            </span>
        </div>

        {{-- Page Heading --}}
        <div class="mb-6 border-b border-slate-200 pb-5">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Profil Saya & Pengaturan
            </h1>

            <p class="mt-2 text-sm text-slate-500 sm:text-base">
                Kelola informasi pribadi, keamanan akun, dan preferensi notifikasi.
            </p>
        </div>

        {{-- Notification --}}
        @if (session('success'))
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m5 12 4 4L19 6"/>
                </svg>

                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="mb-2 font-semibold">
                    Terdapat kesalahan pada formulir:
                </p>

                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Layout --}}
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

            {{-- Left Sidebar --}}
            <aside class="space-y-5 lg:col-span-4">

                @include('pages.profile.components.sidebar')

            </aside>

            {{-- Right Content --}}
            <section class="min-w-0 space-y-5 lg:col-span-8">

                @if ($activeTab === 'profile')

                    @include('pages.profile.components.personal-information')

                    @include('pages.profile.components.upcoming-trip')

                @elseif ($activeTab === 'security')

                    @include('pages.profile.components.security')

                @elseif ($activeTab === 'notifications')

                    @include('pages.profile.components.notifications')

                @endif

            </section>

        </div>

    </main>

    @include('components.footer.footer')

</body>
</html>

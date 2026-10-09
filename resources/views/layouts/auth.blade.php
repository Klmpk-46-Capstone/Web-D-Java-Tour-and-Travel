
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - D'JAVA TOUR AND TRAVEL</title>

    <meta
        name="description"
        content="Masuk dan daftar akun D'JAVA TOUR AND TRAVEL untuk mengelola perjalanan wisata dan pemesanan armada."
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">


    <main class="flex min-h-screen items-center justify-center bg-slate-100 p-3 sm:p-5 lg:p-8">
        <div class="grid w-full max-w-6xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl md:grid-cols-2 lg:rounded-3xl">

            <section class="relative flex flex-col justify-center overflow-hidden bg-teal-800 px-6 py-8 text-white sm:px-8 sm:py-10 lg:px-10 lg:py-12 xl:px-12">
                <div class="absolute inset-0 bg-gradient-to-br from-teal-700 via-teal-800 to-emerald-950"></div>

                <div class="relative z-10">
                    <a href="{{ route('home') }}" class="inline-block text-xs font-bold uppercase tracking-widest text-teal-200 sm:text-sm">
                        DJAVA TOUR AND TRAVEL
                    </a>

                    <h1 class="mt-6 max-w-lg text-3xl font-extrabold leading-tight tracking-tight sm:mt-8 sm:text-4xl lg:text-4xl xl:text-5xl">
                        @yield('hero-title')
                    </h1>

                    @hasSection('hero-description')
                        <p class="mt-4 max-w-lg text-sm leading-relaxed text-teal-50 sm:text-base lg:text-lg">
                            @yield('hero-description')
                        </p>
                    @endif
                </div>

                <div class="relative z-10 mt-8 hidden pt-8 md:block lg:mt-auto lg:pt-16">
                    <p class="max-w-lg text-sm leading-relaxed text-teal-100">
                        Temukan perjalanan terbaik bersama D'JAVA TOUR AND TRAVEL.
                        Layanan rental mobil dan tur privat untuk menemani perjalanan Anda.
                    </p>
                </div>
            </section>

            <section class="flex items-center justify-center px-5 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12 xl:px-12">
                <div class="w-full max-w-lg">
                    @if (session('success'))
                        <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <p class="font-semibold">Periksa kembali data yang kamu masukkan.</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </section>

        </div>
    </main>

</body>
</html>

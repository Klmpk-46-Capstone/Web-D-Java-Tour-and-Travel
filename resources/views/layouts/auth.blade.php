
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

<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-900 antialiased">

    {{-- Navbar --}}
    @include('components.navbar.navbar')

    {{-- Main Content --}}
    <main class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6 sm:py-10">

        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white px-6 py-8 shadow-lg sm:px-9 sm:py-10 lg:px-10 lg:py-10">

            {{-- Notifikasi Berhasil --}}
            @if (session('success'))
                <div
                    role="status"
                    class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Notifikasi Error --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    <p class="font-semibold">
                        Periksa kembali data yang kamu masukkan.
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Login / Register Content --}}
            @yield('content')

        </div>

    </main>

    {{-- Footer --}}
    @include('components.footer.footer')

    {{-- Page Scripts --}}
    @stack('scripts')

</body>
</html>

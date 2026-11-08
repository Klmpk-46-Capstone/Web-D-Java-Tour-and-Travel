<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CMS OPS CONSOLE') - D'Java Tour &amp; Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    @php
        $authUser = auth()->user();
        $isSuper = (bool) $authUser?->isSuperAdmin();
        $roleLabel = $isSuper ? 'Super Admin' : 'Admin Operasional';
        $initials = \Illuminate\Support\Str::of($authUser?->name ?? 'A')
            ->explode(' ')->filter()->take(2)
            ->map(fn ($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w, 0, 1)))
            ->implode('');
        $pendingRefunds = \App\Models\Refund::where('status', 'pending')->count();

        // Notifikasi H-1: trip besok
        $adminH1Trips = \App\Models\Trip::with('package')
            ->whereDate('departure_date', \Carbon\Carbon::tomorrow()->toDateString())
            ->whereIn('status', ['available', 'almost_full', 'full'])
            ->get();
        $adminH1Count = $adminH1Trips->count();

        // Total notifikasi admin (refund pending + h1 trips)
        $totalAdminNotifs = $pendingRefunds + $adminH1Count;
    @endphp

    {{-- TOPBAR BRAND & HEADER --}}
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200/80 px-4 lg:px-6 h-16 flex items-center justify-between shadow-xs">
        {{-- Left: Logo --}}
        <div class="flex items-center gap-3 w-64 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-slate-900 no-underline">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-600 text-white font-black text-lg shadow-sm">
                    ✦
                </span>
                <div class="flex flex-col leading-tight">
                    <span class="font-extrabold text-sm sm:text-base tracking-tight text-slate-900">D'Java Tour</span>
                    <span class="text-[10px] font-bold tracking-widest text-teal-700 uppercase">CMS OPS CONSOLE</span>
                </div>
            </a>
        </div>

        {{-- Center: Global Search Bar --}}
        <div class="flex-1 max-w-xl mx-4 hidden md:block">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ request('q') }}"
                    placeholder="Cari paket wisata, booking ID, atau nama customer..." 
                    class="w-full bg-slate-100 hover:bg-slate-50 focus:bg-white text-xs sm:text-sm text-slate-800 pl-10 pr-4 py-2 rounded-xl border border-transparent focus:border-teal-500 focus:outline-none transition"
                >
            </form>
        </div>

        {{-- Right: Actions & Profile --}}
        <div class="flex items-center gap-3">
            {{-- Real-Time System Clock (WIB) --}}
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-bold text-slate-700 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="admin-live-date">{{ now()->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                <span class="text-slate-300">•</span>
                <span id="admin-live-time" class="font-mono text-teal-800 font-extrabold">{{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}</span>
                <span class="text-[10px] text-slate-400 font-semibold">WIB</span>
            </div>

            {{-- Quick action button --}}
            <button 
                type="button"
                onclick="document.getElementById('modalQuickTrip').classList.remove('hidden')"
                class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-teal-600/30 text-teal-700 bg-teal-50/60 hover:bg-teal-100/70 text-xs font-bold transition shadow-xs cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Buat Trip Baru
            </button>

            {{-- Notification Badge (Dropdown) --}}
            <div class="relative" id="adminNotifWrapper">
                <button
                    type="button"
                    id="adminNotifBtn"
                    class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                    title="{{ $totalAdminNotifs }} notifikasi"
                    aria-expanded="false"
                    aria-controls="adminNotifDropdown"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if ($totalAdminNotifs > 0)
                        <span class="absolute top-1 right-1 min-w-[16px] h-4 flex items-center justify-center px-1 text-[9px] font-extrabold bg-rose-500 text-white rounded-full ring-2 ring-white">
                            {{ $totalAdminNotifs > 9 ? '9+' : $totalAdminNotifs }}
                        </span>
                    @endif
                </button>

                {{-- Dropdown Panel --}}
                <div
                    id="adminNotifDropdown"
                    class="hidden absolute right-0 top-full mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden"
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/80">
                        <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Notifikasi Admin</h4>
                        <span class="text-[10px] font-bold text-slate-400">{{ now()->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                    </div>

                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                        {{-- H-1 Notifications --}}
                        @if ($adminH1Count > 0)
                            <div class="px-4 py-2 bg-amber-50/60">
                                <div class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider mb-2">🔔 Trip Berangkat Besok (H-1)</div>
                                @foreach ($adminH1Trips as $nt)
                                <a href="{{ route('admin.dashboard') }}" class="flex items-start gap-3 py-2 hover:bg-amber-100/60 rounded-lg px-2 transition no-underline group">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-200 text-amber-800 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 truncate group-hover:text-amber-800">
                                            {{ $nt->package->name ?? $nt->batch_name ?? 'Trip #'.$nt->id }}
                                        </div>
                                        <div class="text-[10px] text-slate-500">
                                            Berangkat {{ \Carbon\Carbon::tomorrow()->translatedFormat('d M') }} · {{ $nt->booked_seats }} pax
                                            @if(empty($nt->driver_name))
                                                · <span class="text-rose-600 font-semibold">Driver kosong</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Pending Refunds --}}
                        @if ($pendingRefunds > 0)
                            <div class="px-4 py-2">
                                <div class="text-[10px] font-extrabold text-rose-700 uppercase tracking-wider mb-2">⚠ Refund Menunggu Review</div>
                                <a href="{{ route('admin.refunds.index') }}" class="flex items-center gap-3 py-2 hover:bg-rose-50 rounded-lg px-2 transition no-underline group">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-rose-100 text-rose-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    </span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 group-hover:text-rose-700">{{ $pendingRefunds }} Tiket Refund Ditahan</div>
                                        <div class="text-[10px] text-slate-500">Klik untuk lihat & selesaikan</div>
                                    </div>
                                </a>
                            </div>
                        @endif

                        {{-- Empty State --}}
                        @if ($totalAdminNotifs === 0)
                            <div class="px-4 py-8 text-center">
                                <div class="text-2xl mb-2">✅</div>
                                <div class="text-xs font-semibold text-slate-500">Semua notifikasi sudah ditangani</div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/60 flex justify-between items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-[11px] font-bold text-teal-700 hover:text-teal-900 no-underline">
                            Lihat Dashboard →
                        </a>
                        <span class="text-[10px] text-slate-400 font-semibold">{{ $totalAdminNotifs }} belum selesai</span>
                    </div>
                </div>
            </div>

            {{-- Profil admin yang sedang login --}}
            <div class="relative group">
                <button type="button" class="flex items-center gap-2.5 pl-2 pr-1 py-1 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                    <span class="grid w-8 h-8 place-items-center rounded-full bg-teal-600 text-white text-xs font-extrabold">{{ $initials }}</span>
                    <div class="text-left hidden sm:block leading-tight">
                        <div class="text-xs font-bold text-slate-800">{{ $authUser?->name }}</div>
                        <div class="text-[10px] text-slate-400 font-medium">{{ $roleLabel }}</div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="absolute right-0 mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-lg p-1.5 hidden group-hover:block z-50">
                    <div class="px-3 py-2 border-b border-slate-100 text-xs">
                        <div class="font-bold text-slate-800">{{ $authUser?->name }}</div>
                        <div class="text-[11px] text-slate-400">{{ $authUser?->email }}</div>
                    </div>
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded-lg">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Buka Halaman Depan
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 rounded-lg transition text-left cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar Console
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- WRAPPER BODY WITH SIDEBAR --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- SIDEBAR NAVIGASI --}}
        <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between shrink-0 h-[calc(100vh-4rem)] sticky top-16 overflow-y-auto">
            <div class="p-3.5 space-y-6">
                {{-- MAIN MENU --}}
                <div>
                    <div class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        MAIN MENU
                    </div>
                    <nav class="space-y-1">
                        {{-- Dashboard Analitik --}}
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-teal-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                            <span>Dashboard Analitik</span>
                        </a>

                        {{-- Paket & Itinerary --}}
                        <a 
                            href="{{ route('admin.packages.index') }}" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.packages.index*') && !request()->routeIs('admin.packages.featured') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                            <span>Paket &amp; Itinerary</span>
                        </a>

                        {{-- Armada --}}
                        <a
                            href="{{ route('admin.armada.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.armada.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.armada.*') ? 'text-teal-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h14M6 17l-1-4 2-5h10l2 5-1 4M6 17v2m12-2v2M7 13h10"/></svg>
                            <span>Armada</span>
                        </a>

                        {{-- Unit & Plat --}}
                        <a
                            href="{{ route('admin.units.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.units.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.units.*') ? 'text-teal-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 15h4m3 0h3M7 11h10"/></svg>
                            <span>Unit &amp; Plat</span>
                        </a>

                        {{-- Rekomendasi Utama (Featured) --}}
                        <a 
                            href="{{ route('admin.packages.featured') }}" 
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.packages.featured') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Rekomendasi Utama</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-100 text-teal-800">
                                Featured
                            </span>
                        </a>

                        {{-- Jadwal & Detail Trip --}}
                        <a 
                            href="{{ route('admin.trips.index') }}" 
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.trips.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.trips.*') ? 'text-teal-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span>Jadwal &amp; Detail Trip</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-teal-100 text-teal-800">Trip</span>
                        </a>

                        {{-- Manifes Penumpang (admin_ops & super_admin) --}}
                        <a 
                            href="{{ route('admin.manifest.index') }}" 
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.manifest.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.manifest.*') ? 'text-teal-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span>Manifes Penumpang</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-sky-100 text-sky-800">Ops</span>
                        </a>
                    </nav>
                </div>

                {{-- MANAGEMENT & OPS --}}
                <div>
                    <div class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        MANAGEMENT &amp; OPS
                    </div>
                    <nav class="space-y-1">
                        {{-- Akun Customer --}}
                        <a 
                            href="{{ route('admin.customers.index') }}" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.customers.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg>
                            <span>Akun Customer</span>
                        </a>

                        {{-- Laporan Transaksi & Keuangan --}}
                        <a 
                            href="{{ route('admin.reports.index') }}" 
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.transactions.*') ? 'bg-teal-50 text-teal-800 ring-1 ring-teal-600/20' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.transactions.*') ? 'text-teal-700' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <span>Laporan Transaksi &amp; Keuangan</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-teal-100 text-teal-800">Finance</span>
                        </a>

                        {{-- Refund & Pembatalan --}}
                        <a 
                            href="{{ route('admin.refunds.index') }}" 
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.refunds.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                <span>Refund &amp; Pembatalan</span>
                            </div>
                            @if ($pendingRefunds > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700">
                                    {{ $pendingRefunds }}
                                </span>
                            @endif
                        </a>

                        {{-- Moderasi Ulasan --}}
                        <a 
                            href="{{ route('admin.reviews.index') }}" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.reviews.*') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span>Moderasi Ulasan</span>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- BOTTOM STATUS & CONTROLS --}}
            <div class="p-3.5 border-t border-slate-200/80 space-y-3 bg-slate-50/50">
                <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-white border border-slate-200/60 shadow-xs">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-semibold text-slate-600">Server Operational: Jkt-01</span>
                </div>

                <div class="flex items-center justify-between px-2 text-xs">
                    <a href="https://laravel.com/docs" target="_blank" class="flex items-center gap-1.5 text-slate-500 hover:text-slate-800 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Docs</span>
                    </a>
                    
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 text-slate-500 hover:text-rose-600 transition font-medium cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT VIEWPORT --}}
        <main class="flex-1 overflow-y-auto bg-slate-50/70 p-4 sm:p-6 lg:p-8">
            {{-- Toast Flash Messages --}}
            @if (session('success'))
                <div class="mb-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs sm:text-sm font-semibold text-emerald-800 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-lg leading-none">&times;</button>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-5 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs sm:text-sm font-semibold text-amber-800 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-900 text-lg leading-none">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs sm:text-sm font-semibold text-rose-800 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-lg leading-none">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs sm:text-sm font-semibold text-rose-800 shadow-xs" role="alert">
                    <div class="flex items-center gap-2.5 mb-1.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Data belum dapat disimpan:</span>
                    </div>
                    <ul class="list-disc pl-12 space-y-0.5 font-medium">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- MODAL QUICK TRIP CREATION --}}
    <div id="modalQuickTrip" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in duration-200">
            <button type="button" onclick="document.getElementById('modalQuickTrip').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-2xl font-bold">&times;</button>
            <div class="flex items-center gap-2.5 mb-4">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-teal-100 text-teal-800 text-sm font-bold">✦</span>
                <h3 class="text-lg font-extrabold text-slate-900">Buka Batch / Buat Trip Baru</h3>
            </div>
            <p class="text-xs text-slate-500 mb-5">Jadwal trip yang dibuat di sini akan otomatis tampil dinamis di opsi booking customer.</p>

            <form action="{{ route('admin.trips.store') }}" method="POST" class="space-y-4" id="formQuickTrip">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Paket Wisata</label>
                    <select id="quick_package_id" name="package_id" required class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:border-teal-600">
                        @foreach (\App\Models\Package::where('is_active', true)->get() as $p)
                            <option value="{{ $p->id }}" data-days="{{ $p->durationInDays() }}" data-duration="{{ $p->duration }}">{{ $p->name }} ({{ $p->duration }} • {{ $p->category }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Batch Trip</label>
                    <input type="text" name="batch_name" required placeholder="Cth: Karimunjawa Batch Akhir Pekan #25" class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                <div class="space-y-1">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Keberangkatan</label>
                            <input id="quick_dep_date" type="date" name="departure_date" required min="{{ date('Y-m-d') }}" class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:border-teal-600">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Kepulangan (Otomatis)</label>
                            <input id="quick_ret_date" type="date" name="return_date" class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:border-teal-600">
                        </div>
                    </div>
                    <p id="quick_duration_hint" class="text-[11px] font-semibold text-teal-700 pt-0.5"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Kuota Kursi (Pax)</label>
                        <input type="number" name="quota" required min="1" value="20" class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:border-teal-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Berangkat</label>
                        <input type="time" name="departure_time" class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:bg-white focus:outline-none focus:border-teal-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Titik Kumpul</label>
                    <input type="text" name="meeting_point" maxlength="200" placeholder="Cth: Terminal 1 Bandara Juanda (Gate 2B)" class="w-full text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 focus:bg-white focus:outline-none focus:border-teal-600">
                </div>
                <p class="text-[11px] text-slate-400">Kursi terisi dihitung otomatis dari booking customer. Armada &amp; driver dapat diatur di halaman Jadwal &amp; Detail Trip.</p>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalQuickTrip').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 rounded-xl shadow-sm transition">Simpan &amp; Aktifkan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pkgSelect = document.getElementById('quick_package_id');
            const depInput = document.getElementById('quick_dep_date');
            const retInput = document.getElementById('quick_ret_date');
            const hint = document.getElementById('quick_duration_hint');

            function syncQuickDates() {
                if (!pkgSelect || !depInput || !retInput) return;
                const opt = pkgSelect.options[pkgSelect.selectedIndex];
                if (!opt) return;

                const days = parseInt(opt.dataset.days, 10) || 1;
                const durationLabel = opt.dataset.duration || (days + ' Hari');

                if (depInput.value) {
                    const depDate = new Date(depInput.value + 'T00:00:00');
                    if (!isNaN(depDate.getTime())) {
                        const retDate = new Date(depDate);
                        retDate.setDate(retDate.getDate() + (days - 1));
                        const yyyy = retDate.getFullYear();
                        const mm = String(retDate.getMonth() + 1).padStart(2, '0');
                        const dd = String(retDate.getDate()).padStart(2, '0');
                        retInput.value = `${yyyy}-${mm}-${dd}`;
                        if (hint) {
                            hint.textContent = `✓ Terikat Durasi Paket: ${durationLabel} (Rentang ${days} Hari: ${depInput.value} s/d ${retInput.value})`;
                        }
                    }
                } else if (hint) {
                    hint.textContent = `Durasi paket terpilih: ${durationLabel} (${days} Hari)`;
                }
            }

            if (pkgSelect && depInput) {
                pkgSelect.addEventListener('change', syncQuickDates);
                depInput.addEventListener('change', syncQuickDates);
                syncQuickDates();
            }

            // Admin Notification Dropdown Toggle
            const notifBtn = document.getElementById('adminNotifBtn');
            const notifDropdown = document.getElementById('adminNotifDropdown');
            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isHidden = notifDropdown.classList.contains('hidden');
                    notifDropdown.classList.toggle('hidden', !isHidden);
                    notifBtn.setAttribute('aria-expanded', String(isHidden));
                });

                document.addEventListener('click', function (e) {
                    if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                        notifBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        notifDropdown.classList.add('hidden');
                        notifBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Real-Time System Clock (WIB)
            function updateAdminClock() {
                const timeEl = document.getElementById('admin-live-time');
                const dateEl = document.getElementById('admin-live-date');
                if (!timeEl) return;
                const now = new Date();
                const timeOptions = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                const dateOptions = { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric' };
                timeEl.textContent = new Intl.DateTimeFormat('id-ID', timeOptions).format(now).replace(/\./g, ':');
                if (dateEl) {
                    dateEl.textContent = new Intl.DateTimeFormat('id-ID', dateOptions).format(now);
                }
            }
            setInterval(updateAdminClock, 1000);
            updateAdminClock();
        });
    </script>

    @stack('scripts')
</body>
</html>

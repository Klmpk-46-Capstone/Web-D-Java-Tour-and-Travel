<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gateway Otentikasi Tersembunyi - D'Java Tour CMS OPS CONSOLE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 relative overflow-hidden">
    {{-- Background glowing accents --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        {{-- Branded Card --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-md">
            {{-- Header Badge --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-600 text-white font-black text-lg shadow-sm">
                        ✦
                    </span>
                    <div>
                        <h2 class="text-base font-extrabold text-white tracking-tight leading-none">D'Java Tour</h2>
                        <span class="text-[10px] font-bold tracking-widest text-teal-400 uppercase">CMS OPS CONSOLE</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-950 text-rose-400 border border-rose-800/60 uppercase tracking-wider">
                    Restricted
                </span>
            </div>

            <div class="mb-6">
                <h1 class="text-xl font-extrabold text-white">Login Otorisasi Internal</h1>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                    Portal terenkripsi untuk manajemen operasional, manifes, dan inventory perjalanan.
                </p>
            </div>

            {{-- Flash Messages --}}
            @if (session('warning'))
                <div class="mb-4 p-3 bg-amber-950/80 border border-amber-800/80 rounded-xl text-xs text-amber-300 font-medium">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 p-3 bg-teal-950/80 border border-teal-800/80 rounded-xl text-xs text-teal-300 font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 bg-rose-950/80 border border-rose-800/80 rounded-xl text-xs text-rose-300 font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form Login --}}
            <form action="{{ route('admin.login.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">ID Email Staf Operasional</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email', 'bambang@djavatour.com') }}" 
                        required 
                        autocomplete="email"
                        class="w-full bg-slate-800/90 border border-slate-700 rounded-xl px-4 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                        placeholder="admin@djavatour.com"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Kunci Akses (Password)</label>
                    <input 
                        type="password" 
                        name="password" 
                        value="password123"
                        required 
                        class="w-full bg-slate-800/90 border border-slate-700 rounded-xl px-4 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition"
                        placeholder="••••••••"
                    >
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-200">
                        <input type="checkbox" name="remember" checked class="rounded border-slate-700 bg-slate-800 text-teal-600 focus:ring-teal-500">
                        <span>Ingat Sesi Console</span>
                    </label>
                    <span class="text-[11px] text-teal-400 font-mono">NODE: JKT-01</span>
                </div>

                <button 
                    type="submit" 
                    class="w-full mt-2 py-3 px-4 bg-teal-600 hover:bg-teal-500 text-white text-xs sm:text-sm font-extrabold rounded-xl shadow-lg shadow-teal-900/40 transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Autentikasi &amp; Masuk Console
                </button>
            </form>

            {{-- Demo Hint Box --}}
            <div class="mt-6 pt-5 border-t border-slate-800 text-left">
                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-2">
                    <span class="font-bold text-teal-400">Kredensial Super Admin Bawaan:</span>
                    <span class="font-mono text-slate-500">Mockup Bambang S.</span>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 font-mono text-[11px] text-slate-300 space-y-1">
                    <div>User: <span class="text-teal-300 font-bold">bambang@djavatour.com</span></div>
                    <div>Pass: <span class="text-teal-300 font-bold">password123</span></div>
                </div>
                <div class="mt-3 text-center">
                    <a href="{{ route('home') }}" class="text-[11px] text-slate-500 hover:text-slate-300 transition">
                        &larr; Kembali ke Beranda Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

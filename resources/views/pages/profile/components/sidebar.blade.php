<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- User Information --}}
    <div class="px-5 pb-6 pt-7 text-center sm:px-7">

        <div class="relative mx-auto mb-4 h-24 w-24">

            @if ($profilePhotoUrl)
                <img
                    src="{{ $profilePhotoUrl }}"
                    alt="Foto profil {{ $user->name }}"
                    class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-md"
                >
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-teal-100 text-3xl font-bold text-teal-800 shadow-md">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <span class="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-teal-700 text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/>
                </svg>
            </span>

        </div>

        <h2 class="text-xl font-bold text-slate-900">
            {{ $user->name }}
        </h2>

        <p class="mt-1 break-all text-sm text-slate-500">
            {{ $user->email }}
        </p>

        {{-- Email Verification --}}
        @if ($user->email_verified_at)
            <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 14-5-5 1.4-1.4L11 13.2l5.6-5.6L18 9Z"/>
                </svg>

                Email Terverifikasi
            </div>
        @endif

        {{-- Account Statistics --}}
        <div class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-200 pt-5">

            <div class="rounded-xl bg-slate-50 px-3 py-4">
                <p class="text-2xl font-bold text-teal-700">
                    {{ number_format($completedBookings) }}
                </p>

                <p class="mt-1 text-xs font-medium text-slate-600 sm:text-sm">
                    Pesanan Selesai
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 px-3 py-4">
                <p class="text-2xl font-bold text-slate-900">
                    {{ number_format($reviewCount) }}
                </p>

                <p class="mt-1 text-xs font-medium text-slate-600 sm:text-sm">
                    Total Ulasan
                </p>
            </div>

        </div>

    </div>

</div>

{{-- Sidebar Navigation --}}
<div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">

    <nav class="space-y-2">

        {{-- Personal Information --}}
        <a
            href="{{ localized_route('profile.index', ['tab' => 'profile']) }}"
            @class([
                'flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-semibold transition',
                'bg-teal-800 text-white shadow-sm' => $activeTab === 'profile',
                'text-slate-600 hover:bg-slate-50 hover:text-teal-700' => $activeTab !== 'profile',
            ])
        >
            <span class="flex items-center gap-3">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                </svg>

                Data Pribadi & Kontak
            </span>

            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>

        {{-- Security --}}
        <a
            href="{{ localized_route('profile.index', ['tab' => 'security']) }}"
            @class([
                'flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-semibold transition',
                'bg-teal-800 text-white shadow-sm' => $activeTab === 'security',
                'text-slate-600 hover:bg-slate-50 hover:text-teal-700' => $activeTab !== 'security',
            ])
        >
            <span class="flex items-center gap-3">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>

                Keamanan & Sandi
            </span>

            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>

        {{-- WhatsApp Notifications --}}
        <a
            href="{{ localized_route('profile.index', ['tab' => 'notifications']) }}"
            @class([
                'flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-semibold transition',
                'bg-teal-800 text-white shadow-sm' => $activeTab === 'notifications',
                'text-slate-600 hover:bg-slate-50 hover:text-teal-700' => $activeTab !== 'notifications',
            ])
        >
            <span class="flex items-center gap-3">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>
                </svg>

                Notifikasi WhatsApp
            </span>

            @if (! $user->whatsapp_notifications_enabled)
                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-800">
                    Nonaktif
                </span>
            @endif
        </a>

    </nav>

    <div class="my-3 border-t border-slate-200"></div>

    {{-- Logout --}}
    <form
        action="{{ localized_route('logout') }}"
        method="POST"
    >
        @csrf

        <button
            type="submit"
            class="flex w-full items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <path d="M16 17l5-5-5-5"/>
                <path d="M21 12H9"/>
            </svg>

            Keluar Akun
        </button>
    </form>

</div>

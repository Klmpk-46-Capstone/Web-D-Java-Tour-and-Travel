@php
    $isMobileMenu = ($display ?? 'desktop') === 'mobile';
    $itemClass = $isMobileMenu
        ? 'block rounded-lg px-4 py-3 text-sm text-slate-700 no-underline hover:bg-teal-50 hover:text-teal-700'
        : 'flex items-center gap-3 rounded-xl px-3 py-3 text-slate-700 no-underline transition hover:bg-teal-50 hover:text-teal-700';
@endphp

<a href="{{ url('/profile') }}" class="{{ $itemClass }}">
    Profile
</a>

<a href="{{ url('/settings') }}" class="{{ $itemClass }}">
    Pengaturan
</a>

<div class="my-1 border-t border-slate-100"></div>

<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button
        type="submit"
        class="{{ $isMobileMenu ? 'w-full rounded-lg px-4 py-3 text-left text-sm text-red-600 hover:bg-red-50' : 'flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-red-600 transition hover:bg-red-50' }}"
    >
        Logout
    </button>
</form>

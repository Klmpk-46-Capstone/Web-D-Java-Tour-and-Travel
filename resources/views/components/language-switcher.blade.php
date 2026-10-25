@props(['compact' => false, 'inline' => false])

@php
    $route = request()->route();
    $routeName = $route?->getName() ?? 'home';
    $baseName = str_starts_with($routeName, 'en.')
        ? substr($routeName, 3)
        : $routeName;

    $parameters = $route?->parameters() ?? [];
    $query = request()->getQueryString();

    $idUrl = \Illuminate\Support\Facades\Route::has($baseName)
        ? route($baseName, $parameters)
        : url('/');

    $enUrl = \Illuminate\Support\Facades\Route::has('en.' . $baseName)
        ? route('en.' . $baseName, $parameters)
        : url('/en');

    if ($query) {
        $idUrl .= '?' . $query;
        $enUrl .= '?' . $query;
    }

    $isEnglish = app()->getLocale() === 'en';

    $baseClass = 'rounded-full border px-4 py-2 text-xs font-semibold transition';
    $compactBaseClass = 'rounded-full border px-2 py-1 text-xs font-semibold transition';
    $activeClass = 'border-teal-100 bg-teal-50 text-teal-700';
    $inactiveClass = 'border-slate-200 bg-white text-slate-500 hover:bg-teal-50 hover:text-teal-700';
@endphp

<div class="{{ $inline ? 'flex gap-1' : ($compact ? 'flex items-center gap-1' : 'flex gap-2') }}" aria-label="Pilih bahasa / Choose language">
    <a
        href="{{ $idUrl }}"
        hreflang="id"
        data-lang="id"
        aria-pressed="{{ $isEnglish ? 'false' : 'true' }}"
        class="{{ $compact ? $compactBaseClass : $baseClass }} {{ $isEnglish ? $inactiveClass : $activeClass }}"
    >
        {{ $compact ? 'ID' : 'ID - Indonesia' }}
    </a>

    <a
        href="{{ $enUrl }}"
        hreflang="en"
        data-lang="en"
        aria-pressed="{{ $isEnglish ? 'true' : 'false' }}"
        class="{{ $compact ? $compactBaseClass : $baseClass }} {{ $isEnglish ? $activeClass : $inactiveClass }}"
    >
        {{ $compact ? 'EN' : 'EN - English' }}
    </a>
</div>

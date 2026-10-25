<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Menentukan bahasa aktif dari route group (id/en).
     */
    public function handle(
        Request $request,
        Closure $next,
        string $locale = 'id'
    ): Response {
        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);

        // Keep date formatting (translatedFormat) in the same language.
        \Illuminate\Support\Carbon::setLocale($locale);

        return $next($request);
    }
}

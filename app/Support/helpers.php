<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('localized_route')) {
    /**
     * Membuat URL route sesuai bahasa aktif.
     *
     * Route Indonesia memakai nama asli; route Inggris memakai prefix "en.".
     */
    function localized_route(
        string $name,
        mixed $parameters = [],
        bool $absolute = true
    ): string {
        $localizedName = app()->getLocale() === 'en'
            ? 'en.' . $name
            : $name;

        if (! Route::has($localizedName)) {
            $localizedName = $name;
        }

        return route($localizedName, $parameters, $absolute);
    }
}

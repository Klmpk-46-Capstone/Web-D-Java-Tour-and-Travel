<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Memilih folder view dashboard sesuai bahasa aktif.
     */
    protected function dashboardView(string $view): string
    {
        $folder = app()->getLocale() === 'en'
            ? 'pages.dashboard_en'
            : 'pages.dashboard';

        return $folder . '.' . ltrim($view, '.');
    }
}

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

    /**
     * Pesan validasi untuk form pencarian/filter customer (dashboard & katalog).
     *
     * @return array<string, string>
     */
    protected function catalogValidationMessages(): array
    {
        if (app()->getLocale() === 'en') {
            return [
                'mode.in' => 'The service mode is not valid.',
                'pickup.max' => 'The keyword may not exceed 150 characters.',
                'q.max' => 'The keyword may not exceed 150 characters.',
                'destination.max' => 'The destination is not valid.',
                'departure_date.date' => 'The departure date is not valid.',
                'departure_date.after_or_equal' => 'The departure date cannot be before today.',
                'passengers.integer' => 'Capacity must be a number between 1 and 100.',
                'passengers.min' => 'Capacity must be a number between 1 and 100.',
                'passengers.max' => 'Capacity must be a number between 1 and 100.',
                'kategori.in' => 'The selected category is not valid.',
                'kategori.max' => 'The selected category is not valid.',
                'transmisi.in' => 'The selected transmission is not valid.',
                'urutkan.in' => 'The selected sort order is not valid.',
            ];
        }

        return [
            'mode.in' => 'Mode layanan tidak valid.',
            'pickup.max' => 'Kata kunci maksimal 150 karakter.',
            'q.max' => 'Kata kunci maksimal 150 karakter.',
            'destination.max' => 'Destinasi tidak valid.',
            'departure_date.date' => 'Tanggal keberangkatan tidak valid.',
            'departure_date.after_or_equal' => 'Tanggal keberangkatan tidak boleh sebelum hari ini.',
            'passengers.integer' => 'Kapasitas harus berupa angka antara 1 sampai 100.',
            'passengers.min' => 'Kapasitas harus berupa angka antara 1 sampai 100.',
            'passengers.max' => 'Kapasitas harus berupa angka antara 1 sampai 100.',
            'kategori.in' => 'Kategori yang dipilih tidak valid.',
            'kategori.max' => 'Kategori yang dipilih tidak valid.',
            'transmisi.in' => 'Tipe transmisi yang dipilih tidak valid.',
            'urutkan.in' => 'Urutan yang dipilih tidak valid.',
        ];
    }
}

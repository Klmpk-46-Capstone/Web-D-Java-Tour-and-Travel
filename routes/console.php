<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:send-h1', function () {
    $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();

    $bookings = \App\Models\Booking::with(['trip.package', 'trip.vehicleUnit', 'vehicle', 'vehicleUnit', 'user'])
        ->where(function ($q) use ($tomorrow) {
            $q->whereHas('trip', fn($t) => $t->whereDate('departure_date', $tomorrow))
              ->orWhereDate('rental_date', $tomorrow);
        })
        ->whereIn('booking_status', ['confirmed', 'completed'])
        ->whereIn('payment_status', ['partial', 'paid'])
        ->get();

    $this->info("=== [D'Java Tour] Proses Notifikasi Pengingat H-1 ===");
    $this->info("Tanggal Keberangkatan Besok: " . \Carbon\Carbon::tomorrow()->translatedFormat('d F Y'));
    $this->info("Ditemukan {$bookings->count()} customer yang dijadwalkan berangkat besok.");

    if ($bookings->isEmpty()) {
        $this->comment("Tidak ada keberangkatan yang dijadwalkan untuk besok.");
        return 0;
    }

    $tableData = [];
    foreach ($bookings as $b) {
        $title = $b->trip?->package?->name ?? $b->vehicle?->name ?? 'Trip';
        $plate = $b->revealed_plate ?? 'Belum ditentukan';
        $phone = $b->customer_phone ?? ($b->user?->whatsapp ?? '-');
        $waStatus = ($b->user?->whatsapp_notifications_enabled) ? 'WA Aktif' : 'WA Nonaktif';

        $tableData[] = [
            $b->booking_code,
            $b->customer_name,
            $phone,
            \Illuminate\Support\Str::limit($title, 25),
            $b->pax . ' pax',
            $plate,
            $waStatus,
        ];

        \Illuminate\Support\Facades\Log::info("[Notification H-1] Customer {$b->customer_name} (#{$b->booking_code}) diingatkan untuk trip besok {$title}. Plat: {$plate}.");
    }

    $this->table(['Kode', 'Customer', 'No. HP/WA', 'Paket / Armada', 'Pax', 'Plat Nomor', 'Preferensi WA'], $tableData);
    $this->info("Notifikasi H-1 berhasil disiapkan & dicatat ke log sistem.");
    return 0;
})->purpose('Kirim dan pantau notifikasi pengingat H-1 keberangkatan kepada seluruh customer');

\Illuminate\Support\Facades\Schedule::command('notifications:send-h1')->dailyAt('08:00');

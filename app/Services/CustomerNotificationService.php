<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Refund;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CustomerNotificationService
{
    /**
     * Mengambil seluruh notifikasi aktif untuk customer yang sedang login.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function forUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $notifications = collect();
        $tomorrow = Carbon::tomorrow()->toDateString();
        $today = Carbon::today()->toDateString();

        // 1. Notifikasi H-1: Trip berangkat BESOK
        $h1Bookings = Booking::with(['trip.package', 'vehicle'])
            ->where('user_id', $user->id)
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['partial', 'paid'])
            ->where(function ($q) use ($tomorrow) {
                // Trip paket wisata
                $q->whereHas('trip', fn($t) => $t->whereDate('departure_date', $tomorrow))
                  // Atau rental armada harian
                  ->orWhereDate('rental_date', $tomorrow);
            })
            ->get();

        foreach ($h1Bookings as $b) {
            $title = $b->trip?->package?->name ?? $b->vehicle?->name ?? 'Perjalanan Kamu';
            $departureTime = $b->trip?->departure_time 
                ? Carbon::createFromFormat('H:i:s', $b->trip->departure_time)->format('H:i') . ' WIB'
                : 'Pagi';
            $meetingPoint = $b->pickup_location ?: ($b->trip?->meeting_point ?: 'Titik kumpul yang telah dikonfirmasi');

            $notifications->push([
                'id' => 'h1-' . $b->id,
                'type' => 'h1_reminder',
                'level' => 'urgent',
                'icon' => 'calendar-clock',
                'title' => 'Trip Berangkat Besok!',
                'message' => "Siapkan diri kamu untuk {$title}. Berangkat besok jam {$departureTime}. Titik kumpul: {$meetingPoint}.",
                'booking_code' => $b->booking_code,
                'url' => localized_route('customer.bookings.detail', ['bookingCode' => $b->booking_code]),
                'date' => Carbon::tomorrow()->translatedFormat('d M Y'),
                'is_unread' => true,
            ]);
        }

        // 2. Notifikasi HARI-H: Trip berangkat HARI INI
        $todayBookings = Booking::with(['trip.package', 'vehicle'])
            ->where('user_id', $user->id)
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['partial', 'paid'])
            ->where(function ($q) use ($today) {
                $q->whereHas('trip', fn($t) => $t->whereDate('departure_date', $today))
                  ->orWhereDate('rental_date', $today);
            })
            ->get();

        foreach ($todayBookings as $b) {
            $title = $b->trip?->package?->name ?? $b->vehicle?->name ?? 'Perjalanan Kamu';
            $notifications->push([
                'id' => 'today-' . $b->id,
                'type' => 'today_trip',
                'level' => 'urgent',
                'icon' => 'flag',
                'title' => 'Hari Ini Hari Keberangkatan!',
                'message' => "Selamat menikmati trip {$title}. Jangan lupa bawa identitas dan perlengkapan.",
                'booking_code' => $b->booking_code,
                'url' => localized_route('customer.bookings.detail', ['bookingCode' => $b->booking_code]),
                'date' => Carbon::today()->translatedFormat('d M Y'),
                'is_unread' => true,
            ]);
        }

        // 3. Notifikasi Booking Belum Bayar / Menunggu Pembayaran
        $pendingBookings = Booking::where('user_id', $user->id)
            ->where('payment_status', 'pending')
            ->where('booking_status', 'pending')
            ->latest('id')
            ->limit(2)
            ->get();

        foreach ($pendingBookings as $b) {
            $notifications->push([
                'id' => 'pending-' . $b->id,
                'type' => 'payment_pending',
                'level' => 'warning',
                'icon' => 'credit-card',
                'title' => 'Menunggu Pembayaran',
                'message' => "Selesaikan pembayaran untuk pesanan #{$b->booking_code} agar kursi tidak hangus.",
                'booking_code' => $b->booking_code,
                'url' => localized_route('customer.bookings.detail', ['bookingCode' => $b->booking_code]),
                'date' => $b->created_at->diffForHumans(),
                'is_unread' => false,
            ]);
        }

        // 4. Notifikasi Refund Status Update (7 hari terakhir)
        $recentRefunds = Refund::with('booking')
            ->whereHas('booking', fn($q) => $q->where('user_id', $user->id))
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->latest('updated_at')
            ->limit(2)
            ->get();

        foreach ($recentRefunds as $r) {
            $statusLabel = match ($r->status) {
                'approved' => 'Pengajuan refund kamu telah DISETUJUI.',
                'rejected' => 'Pengajuan refund kamu DITOLAK.',
                'completed', 'refunded' => 'Dana refund telah DITRANSFER.',
                default => 'Pengajuan refund sedang DIPROSES.',
            };

            $notifications->push([
                'id' => 'refund-' . $r->id,
                'type' => 'refund_update',
                'level' => $r->status === 'approved' || $r->status === 'completed' ? 'success' : 'info',
                'icon' => 'refresh',
                'title' => 'Update Pengajuan Refund',
                'message' => "Booking #{$r->booking?->booking_code}: {$statusLabel}",
                'booking_code' => $r->booking?->booking_code,
                'url' => localized_route('customer.bookings.detail', ['bookingCode' => $r->booking?->booking_code]),
                'date' => $r->updated_at->diffForHumans(),
                'is_unread' => false,
            ]);
        }

        return $notifications;
    }
}

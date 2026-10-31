<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;

class RefundService
{
    private const PAID_STATUSES = [
        'paid',
        'success',
        'succeeded',
        'settled',
        'settlement',
        'completed',
        'capture',
    ];

    public function calculate(Booking $booking): array
    {
        $paidAmount = (int) $booking->transactions
            ->filter(fn ($transaction) => in_array(
                strtolower((string) $transaction->status),
                self::PAID_STATUSES,
                true
            ))
            ->sum('amount');

        $departureDate = $booking->trip?->departure_date
            ?? $booking->rental_date;

        if (!$departureDate) {
            return [
                'paidAmount' => $paidAmount,
                'percentage' => 0,
                'refundAmount' => 0,
                'daysRemaining' => null,
                'tier' => 'unavailable',
                'eligible' => false,
            ];
        }

        $today = Carbon::today();
        $departure = Carbon::parse($departureDate)->startOfDay();

        $daysRemaining = (int) $today->diffInDays(
            $departure,
            false
        );

        if ($daysRemaining >= 7) {
            $percentage = 100;
            $tier = 'tier_1';
        } elseif ($daysRemaining >= 3) {
            $percentage = 50;
            $tier = 'tier_2';
        } else {
            $percentage = 0;
            $tier = 'tier_3';
        }

        $refundAmount = (int) round(
            $paidAmount * ($percentage / 100)
        );

        return [
            'paidAmount' => $paidAmount,
            'percentage' => $percentage,
            'refundAmount' => $refundAmount,
            'daysRemaining' => $daysRemaining,
            'tier' => $tier,
            'eligible' => $paidAmount > 0,
        ];
    }

    public function paidStatuses(): array
    {
        return self::PAID_STATUSES;
    }
}

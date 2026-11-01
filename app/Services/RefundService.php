<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;

class RefundService
{
    /**
     * Status transaksi yang dianggap berhasil.
     */
    private const PAID_STATUSES = [
        'paid',
        'success',
        'succeeded',
        'settled',
        'settlement',
        'completed',
        'capture',
    ];

    /**
     * Tahap transaksi DP.
     */
    private const DP_STAGES = [
        'dp',
        'down_payment',
        'downpayment',
    ];

    /**
     * Tahap transaksi pelunasan.
     */
    private const BALANCE_STAGES = [
        'pelunasan',
        'balance',
        'remaining',
        'final_payment',
        'full_payment',
    ];

    /**
     * Menghitung kelayakan dan nominal refund booking.
     */
    public function calculate(Booking $booking): array
    {
        $transactions = $booking->transactions;

        // Ambil hanya transaksi yang benar-benar berhasil.
        $paidTransactions = $transactions->filter(
            fn ($transaction) => in_array(
                strtolower(trim((string) $transaction->status)),
                self::PAID_STATUSES,
                true
            )
        );

        // Hitung total seluruh pembayaran berhasil.
        $paidAmount = (int) $paidTransactions->sum('amount');

        // Periksa pembayaran DP.
        $dpPaid = $paidTransactions->contains(
            fn ($transaction) => in_array(
                strtolower(trim((string) $transaction->payment_stage)),
                self::DP_STAGES,
                true
            )
        );

        // Cari waktu pelunasan yang sudah berhasil.
        $balanceTransaction = $paidTransactions
            ->filter(
                fn ($transaction) => in_array(
                    strtolower(trim((string) $transaction->payment_stage)),
                    self::BALANCE_STAGES,
                    true
                )
            )
            ->sortByDesc(
                fn ($transaction) => $transaction->paid_at
                    ?? $transaction->updated_at
            )
            ->first();

        $balancePaidAt = $balanceTransaction
            ? (
                $balanceTransaction->paid_at
                ?? $balanceTransaction->updated_at
            )
            : null;

        // Tanggal keberangkatan.
        $departureDate = $booking->trip?->departure_date
            ?? $booking->rental_date;

        // Data dasar apabila tanggal keberangkatan belum tersedia.
        if (!$departureDate) {
            return [
                'paidAmount' => $paidAmount,
                'percentage' => 0,
                'refundAmount' => 0,
                'daysRemaining' => null,
                'tier' => 'unavailable',
                'eligible' => $paidAmount > 0 && $dpPaid,
                'dpPaid' => $dpPaid,
                'balancePaidAt' => $balancePaidAt,
            ];
        }

        // Hitung sisa hari menuju keberangkatan.
        $today = Carbon::today();

        $departure = Carbon::parse($departureDate)
            ->startOfDay();

        $daysRemaining = (int) $today->diffInDays(
            $departure,
            false
        );

        // Tentukan persentase refund.
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

        // Hitung nominal refund berdasarkan pembayaran berhasil.
        $refundAmount = (int) round(
            $paidAmount * ($percentage / 100)
        );

        return [
            'paidAmount' => $paidAmount,
            'percentage' => $percentage,
            'refundAmount' => $refundAmount,
            'daysRemaining' => $daysRemaining,
            'tier' => $tier,
            'eligible' => $paidAmount > 0 && $dpPaid,
            'dpPaid' => $dpPaid,
            'balancePaidAt' => $balancePaidAt,
        ];
    }

    /**
     * Mengembalikan daftar status transaksi berhasil.
     */
    public function paidStatuses(): array
    {
        return self::PAID_STATUSES;
    }
}

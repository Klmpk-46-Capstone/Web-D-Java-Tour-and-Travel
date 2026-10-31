<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;

class InvoiceService
{
    private const PAID_STATUSES = [
        'success',
        'paid',
        'succeeded',
        'settled',
        'settlement',
        'completed',
        'capture',
    ];

    private const PAYMENT_STATUSES = [
        'paid',
        'lunas',
        'settled',
        'success',
    ];

    private const PAYMENT_CHANNELS = [
        'BCA' => 'BCA Virtual Account',
        'BNI' => 'BNI Virtual Account',
        'BRI' => 'BRI Virtual Account',
        'MANDIRI' => 'Mandiri Virtual Account',
        'PERMATA' => 'Permata Virtual Account',
        'QRIS' => 'QRIS',
        'DANA' => 'DANA',
        'OVO' => 'OVO',
        'SHOPEEPAY' => 'ShopeePay',
        'CARDS' => 'Credit/Debit Card',
        'ALFAMART' => 'Alfamart',
        'INDOMARET' => 'Indomaret',
    ];

    public function generate(Booking $booking): array
    {
        $paidTransactions = $booking->transactions
            ->filter(fn ($transaction) => in_array(
                strtolower((string) $transaction->status),
                self::PAID_STATUSES,
                true
            ));

        $totalPrice = (int) $booking->total_price;
        $paidAmount = (int) $paidTransactions->sum('amount');

        $remainingAmount = max(0, $totalPrice - $paidAmount);
        $dpAmount = (int) round($totalPrice * 0.30);

        $isPaid = (
            $totalPrice > 0 && $paidAmount >= $totalPrice
        ) || in_array(
            strtolower((string) $booking->payment_status),
            self::PAYMENT_STATUSES,
            true
        );

        $bookingStatus = strtolower(
            (string) $booking->booking_status
        );

        $isCancelled = in_array(
            $bookingStatus,
            ['cancelled', 'canceled'],
            true
        );

        $invoiceStatus = match (true) {
            $isCancelled => 'cancelled',
            $isPaid => 'paid',
            $paidAmount > 0 => 'partial',
            default => 'unpaid',
        };

        $package = $booking->package;
        $trip = $booking->trip;

        $invoiceDate = $booking->created_at
            ? $booking->created_at->translatedFormat('d F Y')
            : '-';

        $travelDate = $trip?->departure_date
            ? Carbon::parse($trip->departure_date)
                ->translatedFormat('d F Y')
            : '-';

        if ($trip?->return_date) {
            $travelDate .= ' - ' . Carbon::parse($trip->return_date)
                ->translatedFormat('d F Y');
        }

        $latestPaidTransaction = $paidTransactions
            ->sortByDesc('id')
            ->first();

        $rawPaymentMethod = $latestPaidTransaction?->payment_channel
            ?: $latestPaidTransaction?->payment_method
            ?: $booking->payment_method
            ?: '-';

        $paymentMethod = self::PAYMENT_CHANNELS[
            strtoupper($rawPaymentMethod)
        ] ?? $rawPaymentMethod;

        $company = [
            'name' => config('app.company_name') ?: "D'JAVA TOUR AND TRAVEL",
            'address' => config('app.company_address'),
            'phone' => config('app.company_phone'),
            'email' => config('app.company_email'),
            'website' => config('app.company_website'),
            'bank_name' => config('app.company_bank_name'),
            'bank_account' => config('app.company_bank_account'),
            'bank_holder' => config('app.company_bank_holder'),
        ];

        return [
            'booking' => $booking,
            'package' => $package,
            'trip' => $trip,
            'paidTransactions' => $paidTransactions,
            'totalPrice' => $totalPrice,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'dpAmount' => $dpAmount,
            'isPaid' => $isPaid,
            'isCancelled' => $isCancelled,
            'invoiceStatus' => $invoiceStatus,
            'invoiceDate' => $invoiceDate,
            'travelDate' => $travelDate,
            'invoiceNumber' => 'INV-' . $booking->booking_code,
            'invoiceService' => $package?->name
                ?? $booking->selected_armada
                ?? 'Layanan perjalanan',
            'invoiceDuration' => $package?->duration ?? '-',
            'company' => $company,
            'paymentMethod' => $paymentMethod,
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $dateRange = $request->query('range', '30'); // 7, 30, 90, all

        $query = Transaction::with(['booking.package', 'booking.trip', 'booking.user'])
            ->latest('id');

        if ($dateRange !== 'all') {
            $days = (int) $dateRange;
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        if ($status) {
            $query->where('status', $status);
        }

        $transactions = $query->paginate(15);
        $totalRevenue = Transaction::where('status', 'success')->sum('amount');
        $successfulCount = Transaction::where('status', 'success')->count();
        $refundedCount = Transaction::where('status', 'refunded')->count();

        return view('admin.reports.index', compact(
            'transactions',
            'status',
            'dateRange',
            'totalRevenue',
            'successfulCount',
            'refundedCount'
        ));
    }

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'Laporan_Transaksi_DJava_Tour_' . date('Y-m-d_His') . '.csv';
        $transactions = Transaction::with(['booking.package', 'booking.trip'])->latest('id')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Kode Transaksi',
                'Kode Booking',
                'Pelanggan',
                'Email',
                'Paket Wisata',
                'Jadwal Trip',
                'Pax',
                'Jumlah (IDR)',
                'Metode Pembayaran',
                'Status Transaksi',
                'Waktu Pembayaran',
            ]);

            foreach ($transactions as $t) {
                $booking = $t->booking;
                fputcsv($file, [
                    $t->transaction_code,
                    $booking ? $booking->booking_code : '-',
                    $booking ? $booking->customer_name : '-',
                    $booking ? $booking->customer_email : '-',
                    $booking && $booking->package ? $booking->package->name : '-',
                    $booking && $booking->trip ? $booking->trip->departure_date->format('d/m/Y') : '-',
                    $booking ? $booking->pax : 0,
                    $t->amount,
                    $t->payment_method,
                    strtoupper($t->status),
                    $t->paid_at ? $t->paid_at->format('Y-m-d H:i:s') : '-',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}

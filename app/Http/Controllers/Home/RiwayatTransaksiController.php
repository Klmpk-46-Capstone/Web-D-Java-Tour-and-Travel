<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Refund;
use App\Models\Review;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RiwayatTransaksiController extends Controller
{
    /**
     * Menampilkan riwayat booking milik pengguna yang sedang login.
     */
    public function customerBookings(Request $request): View
    {
        $user = $request->user();

        $query = Booking::query()
            ->with([
                'package',
                'trip',
                'vehicle',
                'review',
                'transactions' => fn ($transactionQuery) => $transactionQuery
                    ->latest('id'),
                'refund',
            ])
            ->where(function ($bookingQuery) use ($user) {
                $bookingQuery->where('user_id', $user->id);

                // Mendukung booking lama yang belum terhubung user_id,
                // tetapi hanya jika email booking cocok dengan akun login.
                $bookingQuery->orWhere(function ($legacyQuery) use ($user) {
                    $legacyQuery
                        ->whereNull('user_id')
                        ->where('customer_email', $user->email);
                });
            });

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('booking_code', 'like', '%' . $search . '%')
                    ->orWhereHas('package', function ($packageQuery) use ($search) {
                        $packageQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('trip', function ($tripQuery) use ($search) {
                        $tripQuery->where('batch_name', 'like', '%' . $search . '%');
                    })
                    ->orWhere('selected_armada', 'like', '%' . $search . '%');
            });
        }

        match ($status) {
            'waiting_dp' => $query->whereIn(
                'payment_status',
                ['pending', 'unpaid', 'waiting_dp']
            ),
            'confirmed' => $query->where(function ($statusQuery) {
                $statusQuery
                    ->whereIn('booking_status', ['confirmed', 'completed'])
                    ->orWhereIn('payment_status', ['partial', 'paid']);
            }),
            'completed' => $query->where(function ($statusQuery) {
                $statusQuery
                    ->whereIn('booking_status', ['completed', 'finished'])
                    ->orWhere('payment_status', 'paid');
            }),
            default => null,
        };

        $bookings = $query
            ->latest('created_at')
            ->paginate(8)
            ->withQueryString();

        // Angka pada filter dihitung dari booking milik pengguna yang login.
        $countQuery = Booking::query()
            ->where(function ($bookingQuery) use ($user) {
                $bookingQuery->where('user_id', $user->id)
                    ->orWhere(function ($legacyQuery) use ($user) {
                        $legacyQuery->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            });

        $counts = [
            'all' => (clone $countQuery)->count(),
            'waiting_dp' => (clone $countQuery)
                ->whereIn('payment_status', ['pending', 'unpaid', 'waiting_dp'])
                ->count(),
            'confirmed' => (clone $countQuery)
                ->where(function ($query) {
                    $query->whereIn('booking_status', ['confirmed', 'completed'])
                        ->orWhereIn('payment_status', ['partial', 'paid']);
                })->count(),
            'completed' => (clone $countQuery)
                ->where(function ($query) {
                    $query->whereIn('booking_status', ['completed', 'finished'])
                        ->orWhere('payment_status', 'paid');
                })->count(),
        ];

        return view($this->dashboardView('riwayat_transaksi.index'), [
            'bookings' => $bookings,
            'counts' => $counts,
            'activeStatus' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Menampilkan detail booking milik pengguna yang sedang login.
     */
    public function detail(
        Request $request,
        string $bookingCode
    ): View {
        $user = $request->user();

        $booking = Booking::query()
            ->with([
                'package',
                'trip',
                'vehicle',
                'transactions' => fn ($query) => $query->latest('id'),
                'refund',
            ])
            ->where('booking_code', $bookingCode)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            })
            ->firstOrFail();

        $paidStatuses = [
            'success',
            'paid',
            'settled',
            'settlement',
            'completed',
            'capture',
        ];

        $paidTransactions = $booking->transactions->filter(
            fn ($transaction) => in_array(
                strtolower((string) $transaction->status),
                $paidStatuses,
                true
            )
        );

        $paidAmount = (int) $paidTransactions->sum('amount');
        $totalPrice = (int) $booking->total_price;
        $dpAmount = (int) round($totalPrice * 0.30);
        $remainingAmount = max(0, $totalPrice - $paidAmount);

        $paidStages = $paidTransactions
            ->pluck('payment_stage')
            ->map(fn ($stage) => strtolower((string) $stage));

        $dpPaid = $paidStages->contains('dp');
        $pelunasanPaid = $paidStages->contains('pelunasan');

        // Status lunas dihitung dari transaksi yang benar-benar berhasil atau status payment lunas.
        $isPaid = ($remainingAmount === 0 && $paidAmount >= $totalPrice && $totalPrice > 0)
            || in_array(strtolower((string) $booking->payment_status), ['paid', 'lunas', 'settled', 'success'], true);

        $bookingStatus = strtolower((string) $booking->booking_status);

        $isCancelled = in_array(
            $bookingStatus,
            ['cancelled', 'canceled'],
            true
        );

        $isRefundRequested = $bookingStatus === 'refund_requested';

        $isCompleted = in_array(
            $bookingStatus,
            ['completed', 'finished'],
            true
        );

        $canPay = !$isCancelled
            && !$isRefundRequested
            && !$isCompleted
            && !$isPaid;

        $paymentStage = $dpPaid ? 'pelunasan' : 'dp';

        $billAmount = $dpPaid
            ? $remainingAmount
            : $dpAmount;

        $billAmount = $isPaid ? 0 : $billAmount;

        $latestTransaction = $booking->transactions->first();

        $hasReviewed = Review::query()
            ->where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->exists();

        return view(
            $this->dashboardView('riwayat_transaksi.detail'),
            compact(
                'booking',
                'paidAmount',
                'totalPrice',
                'dpAmount',
                'remainingAmount',
                'dpPaid',
                'pelunasanPaid',
                'isPaid',
                'isCancelled',
                'isRefundRequested',
                'isCompleted',
                'canPay',
                'paymentStage',
                'billAmount',
                'latestTransaction',
                'hasReviewed'
            )
        );
    }

    /**
     * Menampilkan invoice booking milik pengguna yang sedang login.
     */
    public function invoice(
        Request $request,
        string $bookingCode,
        InvoiceService $invoiceService
    ): View {
        $user = $request->user();

        $booking = Booking::query()
            ->with([
                'package',
                'trip',
                'vehicle',
                'transactions' => fn ($query) => $query->latest('id'),
                'refund',
            ])
            ->where('booking_code', $bookingCode)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacyQuery) use ($user) {
                        $legacyQuery
                            ->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            })
            ->firstOrFail();

        $invoiceData = $invoiceService->generate($booking);

        return view(
            $this->dashboardView('riwayat_transaksi.invoice'),
            $invoiceData
        );
    }

    /**
     * Mengirim permintaan pembatalan dan refund untuk booking milik pengguna.
     */
    public function requestRefund(
        Request $request,
        string $bookingCode
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $user = $request->user();

        $booking = Booking::query()
            ->where('booking_code', $bookingCode)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacyQuery) use ($user) {
                        $legacyQuery->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            })
            ->firstOrFail();

        if (in_array($booking->booking_status, [
            'refund_requested',
            'cancelled',
            'completed',
            'finished',
        ], true)) {
            return back()->with(
                'warning',
                app()->getLocale() === 'en'
                    ? 'A refund cannot be requested for this booking in its current status.'
                    : 'Booking ini tidak dapat diajukan refund karena statusnya saat ini.'
            );
        }

        DB::transaction(function () use ($booking, $validated) {
            $existingRefund = Refund::query()
                ->where('booking_id', $booking->id)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();

            if ($existingRefund) {
                return;
            }

            Refund::create([
                'booking_id' => $booking->id,
                'refund_code' => 'REF-' . substr($booking->booking_code, 4),
                'reason' => $validated['reason'],
                'amount' => $booking->total_price,
                'status' => 'pending',
            ]);

            $booking->update([
                'booking_status' => 'refund_requested',
            ]);
        });

        return back()->with(
            'success',
            app()->getLocale() === 'en'
                ? 'The cancellation and refund request has been processed if no active request existed.'
                : 'Permintaan pembatalan dan refund berhasil diproses jika belum ada pengajuan aktif.'
        );
    }
}

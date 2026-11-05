<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Trip;
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
                'trip' => fn ($query) => $query->with(['vehicle', 'vehicleUnit']),
                'vehicle',
                'vehicleUnit',
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
                'trip' => fn ($query) => $query->with(['vehicle', 'vehicleUnit']),
                'vehicle',
                'vehicleUnit',
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

        // =====================================================
        // REFUND ELIGIBILITY
        // =====================================================

        $refundStatus = strtolower(trim(
            (string) ($booking->refund?->status ?? '')
        ));

        // Ambil transaksi pelunasan yang berhasil.
        $latestBalanceTransaction = $paidTransactions
            ->filter(fn ($transaction) =>
                strtolower((string) $transaction->payment_stage) === 'pelunasan'
            )
            ->sortByDesc(fn ($transaction) =>
                $transaction->paid_at?->timestamp
                ?? $transaction->created_at?->timestamp
                ?? 0
            )
            ->first();

        // Tanggal pelunasan berhasil.
        $balancePaidAt = $latestBalanceTransaction?->paid_at
            ?? $latestBalanceTransaction?->created_at;

        // H+7 dihitung hanya jika pelunasan sudah dilakukan.
        $refundDeadline = $balancePaidAt
            ? \Illuminate\Support\Carbon::parse($balancePaidAt)
                ->addDays(7)
                ->endOfDay()
            : null;

        // Status yang mengunci pengajuan refund.
        $refundLockedStatuses = [
            'pending',
            'processing',
            'approved',
            'refunded',
        ];

        $hasActiveOrApprovedRefund = in_array(
            $refundStatus,
            $refundLockedStatuses,
            true
        );

        // Refund dapat diajukan setelah DP berhasil.
        // Jika pelunasan sudah dilakukan, batas H+7 berlaku.
        $canRequestRefund = $dpPaid
            && !$isCancelled
            && !$isRefundRequested
            && !$hasActiveOrApprovedRefund
            && (
                $refundDeadline === null
                || now()->lte($refundDeadline)
            );

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
                'hasReviewed',
                'canRequestRefund',
                'refundDeadline',
                'balancePaidAt'
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
                'trip' => fn ($query) => $query->with(['vehicle', 'vehicleUnit']),
                'vehicle',
                'vehicleUnit',
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
     * Membatalkan booking yang belum memiliki pembayaran berhasil.
     *
     * Booking paket wisata akan mengembalikan kuota peserta.
     * Booking armada akan melepaskan jadwal kendaraan melalui
     * perubahan status booking menjadi cancelled.
     */
    public function cancelBooking(
        Request $request,
        string $bookingCode
    ): RedirectResponse {
        $user = $request->user();
        $isEnglish = app()->getLocale() === 'en';

        DB::transaction(function () use ($user, $bookingCode, $isEnglish) {
            $booking = Booking::query()
                ->where('booking_code', $bookingCode)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere(function ($legacyQuery) use ($user) {
                            $legacyQuery
                                ->whereNull('user_id')
                                ->where('customer_email', $user->email);
                        });
                })
                ->lockForUpdate()
                ->firstOrFail();

            $bookingStatus = strtolower(
                (string) $booking->booking_status
            );

            if (in_array($bookingStatus, [
                'cancelled',
                'canceled',
            ], true)) {
                abort(422, $isEnglish
                    ? 'This booking has already been cancelled.'
                    : 'Booking ini sudah dibatalkan.');
            }

            if (in_array($bookingStatus, [
                'completed',
                'finished',
                'refund_requested',
            ], true)) {
                abort(422, $isEnglish
                    ? 'This booking cannot be cancelled directly.'
                    : 'Booking ini tidak dapat dibatalkan secara langsung.');
            }

            $hasSuccessfulPayment = $booking->transactions()
                ->whereIn('status', [
                    'success',
                    'paid',
                    'succeeded',
                    'settled',
                    'settlement',
                    'completed',
                    'capture',
                ])
                ->exists();

            if ($hasSuccessfulPayment) {
                abort(422, $isEnglish
                    ? 'This booking already has a successful payment. Please submit a refund request instead.'
                    : 'Booking ini sudah memiliki pembayaran berhasil. Silakan ajukan refund.');
            }

            $hasRefund = Refund::query()
                ->where('booking_id', $booking->id)
                ->whereIn('status', [
                    'pending',
                    'processing',
                    'approved',
                    'refunded',
                ])
                ->exists();

            if ($hasRefund) {
                abort(422, $isEnglish
                    ? 'This booking has an active refund process.'
                    : 'Booking ini sedang memiliki proses refund aktif.');
            }

            /*
             * Kembalikan kuota paket wisata.
             * Pengurangan dilakukan hanya setelah booking dipastikan
             * belum dibatalkan dan belum memiliki pembayaran berhasil.
             */
            if ($booking->trip_id) {
                $trip = Trip::query()
                    ->whereKey($booking->trip_id)
                    ->lockForUpdate()
                    ->first();

                if ($trip) {
                    $trip->booked_seats = max(
                        0,
                        (int) $trip->booked_seats - (int) $booking->pax
                    );

                    $trip->status = $trip->resolveStatus();
                    $trip->save();
                }
            }

            /*
             * Tandai transaksi yang belum berhasil sebagai cancelled.
             * Transaksi yang sudah berhasil tidak boleh dibatalkan di sini.
             */
            $booking->transactions()
                ->whereIn('status', [
                    'pending',
                    'unpaid',
                    'waiting',
                    'created',
                ])
                ->update([
                    'status' => 'cancelled',
                ]);

            $booking->update([
                'booking_status' => 'cancelled',
            ]);
        });

        return redirect()
            ->to(localized_route('customer.bookings.detail', [
                'bookingCode' => $bookingCode,
            ]))
            ->with(
                'success',
                $isEnglish
                    ? 'Your booking has been cancelled successfully. The reserved availability has been released.'
                    : 'Booking berhasil dibatalkan. Kuota atau ketersediaan pemesanan telah dilepaskan.'
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

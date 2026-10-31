<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(
        private readonly RefundService $refundService
    ) {
    }

    /**
     * Menampilkan halaman pengajuan refund customer.
     */
    public function index(
        Request $request,
        string $bookingCode
    ): View {
        $booking = $this->findCustomerBooking($request, $bookingCode);

        $booking->load([
            'package',
            'trip',
            'vehicle',
            'transactions',
            'refund',
        ]);

        $refundData = $this->refundService->calculate($booking);

        return view(
            $this->localizedView('pages.dashboard.refund.index'),
            [
                'booking' => $booking,
                'refund' => $booking->refund,
                'refundData' => $refundData,
            ]
        );
    }

    /**
     * Menyimpan pengajuan refund customer.
     */
    public function store(
        Request $request,
        string $bookingCode
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => [
                'required',
                Rule::in([
                    'schedule_change',
                    'weather',
                    'emergency',
                    'other',
                ]),
            ],
            'reason_detail' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'bank_name' => [
                'required',
                'string',
                'max:100',
            ],
            'bank_account' => [
                'required',
                'string',
                'regex:/^[0-9]{8,25}$/',
            ],
            'account_holder' => [
                'required',
                'string',
                'max:150',
            ],
            'agreement' => [
                'accepted',
            ],
        ]);

        $booking = $this->findCustomerBooking($request, $bookingCode);

        DB::transaction(function () use (
            $booking,
            $validated
        ) {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            $activeRefund = Refund::query()
                ->where('booking_id', $lockedBooking->id)
                ->whereIn('status', [
                    'pending',
                    'processing',
                    'approved',
                ])
                ->exists();

            if ($activeRefund) {
                abort(409, 'Pengajuan refund untuk booking ini masih diproses.');
            }

            if (in_array($lockedBooking->booking_status, [
                'cancelled',
                'canceled',
                'completed',
                'finished',
            ], true)) {
                abort(422, 'Booking ini tidak dapat diajukan refund.');
            }

            $lockedBooking->load([
                'trip',
                'transactions',
            ]);

            $refundData = $this->refundService->calculate(
                $lockedBooking
            );

            if (!$refundData['eligible']) {
                abort(422, 'Belum terdapat pembayaran berhasil untuk booking ini.');
            }

            $reasonLabels = [
                'schedule_change' => 'Perubahan Jadwal',
                'weather' => 'Cuaca Buruk / Bencana',
                'emergency' => 'Keperluan Mendesak',
                'other' => 'Alasan Lainnya',
            ];

            $reason = $reasonLabels[$validated['reason']];

            if (!empty($validated['reason_detail'])) {
                $reason .= ': ' . $validated['reason_detail'];
            }

            Refund::create([
                'booking_id' => $lockedBooking->id,
                'refund_code' => 'REF-' . Str::upper(
                    Str::random(10)
                ),
                'reason' => $reason,
                'amount' => $refundData['refundAmount'],
                'status' => 'pending',
                'bank_name' => $validated['bank_name'],
                'bank_account' => $validated['bank_account'],
                'account_holder' => $validated['account_holder'],
            ]);

            $lockedBooking->update([
                'booking_status' => 'refund_requested',
            ]);
        });

        return redirect()
            ->route('customer.bookings.detail', [
                'bookingCode' => $bookingCode,
            ])
            ->with(
                'success',
                app()->getLocale() === 'en'
                    ? 'Your refund request has been submitted and is waiting for admin review.'
                    : 'Pengajuan refund berhasil dikirim dan sedang menunggu pemeriksaan admin.'
            );
    }

    /**
     * Admin menyetujui atau menolak pengajuan refund.
     */
    public function review(
        Request $request,
        int $refundId
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'decision' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],
            'admin_notes' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
            'amount' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $refundId,
            $validated
        ) {
            $refund = Refund::query()
                ->with('booking.transactions')
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'pending') {
                abort(422, 'Pengajuan ini sudah pernah ditinjau.');
            }

            $paidAmount = $refund->booking->transactions
                ->filter(fn ($transaction) => in_array(
                    strtolower((string) $transaction->status),
                    $this->refundService->paidStatuses(),
                    true
                ))
                ->sum('amount');

            $amount = (int) (
                $validated['amount'] ?? $refund->amount
            );

            if (
                $validated['decision'] === 'approved'
                && $amount > $paidAmount
            ) {
                abort(422, 'Nominal refund melebihi total pembayaran berhasil.');
            }

            $refund->update([
                'status' => $validated['decision'],
                'amount' => $validated['decision'] === 'approved'
                    ? $amount
                    : 0,
                'admin_notes' => $validated['admin_notes'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($validated['decision'] === 'rejected') {
                $refund->booking->update([
                    'booking_status' => 'confirmed',
                ]);
            }
        });

        return back()->with(
            'success',
            'Keputusan refund berhasil disimpan.'
        );
    }

    /**
     * Admin mengonfirmasi transfer manual yang sudah dilakukan.
     */
    public function markTransferred(
        Request $request,
        int $refundId
    ): RedirectResponse {
        $this->ensureAdmin($request);

        DB::transaction(function () use ($refundId) {
            $refund = Refund::query()
                ->with('booking')
                ->lockForUpdate()
                ->findOrFail($refundId);

            if ($refund->status !== 'approved') {
                abort(422, 'Hanya refund yang disetujui yang dapat ditandai sudah ditransfer.');
            }

            $refund->update([
                'status' => 'refunded',
                'transferred_at' => now(),
            ]);

            $refund->booking->update([
                'booking_status' => 'cancelled',
            ]);
        });

        return back()->with(
            'success',
            'Transfer refund berhasil dikonfirmasi.'
        );
    }

    /**
     * Memastikan booking merupakan milik customer yang login.
     */
    private function findCustomerBooking(
        Request $request,
        string $bookingCode
    ): Booking {
        $user = $request->user();

        return Booking::query()
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
    }

    /**
     * Memastikan tindakan hanya dapat dilakukan oleh admin.
     */
    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            $request->user()
                && $request->user()->role === 'admin',
            403
        );
    }

    /**
     * Memilih Blade sesuai locale aplikasi.
     */
    private function localizedView(string $view): string
    {
        if (app()->getLocale() === 'en') {
            return str_replace(
                'pages.dashboard.refund.index',
                'pages.dashboard_en.refund.index',
                $view
            );
        }

        return $view;
    }
}

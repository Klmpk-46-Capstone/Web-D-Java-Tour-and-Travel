<?php

namespace App\Http\Controllers\Pembayaran;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Services\XenditPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PembayaranController extends Controller
{
    private const PAID_STATUSES = [
        'paid',
        'settled',
        'settlement',
        'completed',
        'success',
        'capture',
    ];
    private const XENDIT_SUCCEEDED = 'SUCCEEDED';

    public function mulaiPembayaran(
        Request $request,
        string $bookingCode
    ): View|RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);
        $booking->load('transactions');

        if (in_array($booking->booking_status, [
            'cancelled', 'canceled', 'refund_requested',
            'completed', 'finished',
        ], true)) {
            return $this->bookingRedirect(
                'error',
                'Booking ini tidak dapat dibayar.'
            );
        }

        $paidStages = $booking->transactions
            ->filter(fn ($transaction) => in_array(
                strtolower((string) $transaction->status),
                self::PAID_STATUSES,
                true
            ))
            ->pluck('payment_stage')
            ->unique();

        $dpPaid = $paidStages->contains('dp');
        $pelunasanPaid = $paidStages->contains('pelunasan');

        if ($dpPaid && $pelunasanPaid) {
            return $this->bookingRedirect(
                'success',
                'Booking ini sudah lunas.'
            );
        }

        // Tahap pembayaran ditentukan otomatis.
        $stage = $dpPaid ? 'pelunasan' : 'dp';

        // Jika instruksi untuk tahap ini sudah ada dan belum lunas,
        // arahkan pengguna ke instruksi yang sama.
        $pendingTransaction = $booking->transactions()
            ->where('payment_stage', $stage)
            ->whereNotNull('xendit_payment_request_id')
            ->whereNotIn('status', self::PAID_STATUSES)
            ->latest('id')
            ->first();

        if ($pendingTransaction) {
            return redirect()->route('pembayaran.instruksi', [
                'bookingCode' => $booking->booking_code,
                'stage' => $stage,
            ]);
        }

        $totalPrice = (int) $booking->total_price;

        $paidSum = (int) $booking->transactions()
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('amount');

        $amount = $stage === 'dp'
            ? (int) round($totalPrice * 0.30)
            : max(0, $totalPrice - $paidSum);

        return view(
            'components.pembayaran.metode-pembayaran',
            compact('booking', 'stage', 'amount')
        );
    }

    public function buatPembayaran(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ): RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        if (in_array($booking->booking_status, [
            'cancelled', 'canceled', 'refund_requested',
            'completed', 'finished',
        ], true)) {
            return back()->with('error', 'Booking ini tidak dapat dibayar.');
        }

        $validated = $request->validate([
            'channel_code' => ['required', 'string', 'max:50'],
        ]);

        $paidStages = $booking->transactions()
            ->whereIn('status', self::PAID_STATUSES)
            ->pluck('payment_stage')
            ->unique();

        $dpPaid = $paidStages->contains('dp');
        $pelunasanPaid = $paidStages->contains('pelunasan');

        if ($dpPaid && $pelunasanPaid) {
            return $this->bookingRedirect(
                'success',
                'Booking ini sudah lunas.'
            );
        }

        // Server menentukan tahap pembayaran, bukan input pengguna.
        $stage = $dpPaid ? 'pelunasan' : 'dp';

        $channel = strtoupper($validated['channel_code']);

        $groups = config('payment_methods.groups', []);
        $availableChannels = [];

        foreach ($groups as $group) {
            foreach (($group['methods'] ?? []) as $code => $label) {
                $availableChannels[] = $code;
            }
        }

        if (!in_array($channel, $availableChannels, true)) {
            return back()->withErrors([
                'channel_code' => 'Metode pembayaran ini belum diaktifkan.',
            ])->withInput();
        }

        if (in_array($channel, ['CARDS'], true)) {
            return back()->with(
                'error',
                'Pembayaran kartu belum diaktifkan. Konfigurasikan alur kartu dan autentikasi terlebih dahulu.'
            );
        }

        if (str_starts_with($channel, 'DIRECT_DEBIT_')
            || str_starts_with($channel, 'DD_')) {
            return back()->with(
                'error',
                'Direct Debit memerlukan proses penautan akun dan token pembayaran terlebih dahulu.'
            );
        }

        $totalPrice = (int) $booking->total_price;

        $paidSum = (int) $booking->transactions()
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('amount');

        $amount = $stage === 'dp'
            ? (int) round($totalPrice * 0.30)
            : max(0, $totalPrice - $paidSum);

        if ($amount < 1) {
            return back()->with('error', 'Nominal pembayaran tidak valid.');
        }

        $latest = $booking->transactions()
            ->where('payment_stage', $stage)
            ->latest('id')
            ->first();

        if (
            $latest &&
            in_array(
                strtolower((string) $latest->status),
                self::PAID_STATUSES,
                true
            )
        ) {
            return $this->bookingRedirect(
                'success',
                'Tahap pembayaran ini sudah berhasil dibayar.'
            );
        }

        $existingPending = $booking->transactions()
            ->where('payment_stage', $stage)
            ->whereNotNull('xendit_payment_request_id')
            ->whereIn('status', ['pending', 'requires_action'])
            ->latest('id')
            ->first();

        if ($existingPending) {
            return redirect()->route('pembayaran.instruksi', [
                'bookingCode' => $booking->booking_code,
                'stage' => $stage,
            ]);
        }

        $payload = [
            'reference_id' => $booking->booking_code
                . '-' . strtoupper($stage) . '-' . uniqid('', true),
            'type' => 'PAY',
            'country' => 'ID',
            'currency' => 'IDR',
            'request_amount' => $amount,
            'channel_code' => $channel,
            'channel_properties' => [],
            'description' => ($stage === 'dp' ? 'DP 30%' : 'Pelunasan 70%')
                . ' booking ' . $booking->booking_code,
            'metadata' => [
                'booking_code' => $booking->booking_code,
                'payment_stage' => $stage,
            ],
        ];

        if (in_array($channel, [
            'BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA',
        ], true)) {
            $payload['channel_properties'] = [
                'customer_name' => substr(
                    $booking->customer_name ?: 'Pelanggan DJava',
                    0,
                    50
                ),
            ];
        }

        if (in_array($channel, [
            'DANA', 'OVO', 'SHOPEEPAY',
        ], true)) {
            $payload['channel_properties'] = [
                'success_return_url' => route('customer.bookings.index'),
            ];
        }

        try {
            $result = $xendit->createPaymentRequest($payload);
        } catch (Throwable $e) {
            report($e);
            Log::error('Xendit gagal membuat payment request.', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Gagal membuat pembayaran. Periksa channel dan konfigurasi Xendit.'
            );
        }

        $requestId = $result['payment_request_id']
            ?? $result['id']
            ?? null;

        if (!$requestId) {
            return back()->with(
                'error',
                'Xendit tidak mengembalikan ID pembayaran.'
            );
        }

        $instructions = [
            'status' => $result['status'] ?? 'REQUIRES_ACTION',
            'actions' => $result['actions'] ?? [],
            'channel_code' => $channel,
            'reference_id' => $result['reference_id'] ?? null,
        ];

        DB::transaction(function () use (
            $booking, $stage, $amount, $channel,
            $requestId, $result, $instructions
        ) {
            $booking->transactions()->create([
                'transaction_code' => 'TRX-' . strtoupper(uniqid('', true)),
                'amount' => $amount,
                'payment_method' => $channel,
                'payment_channel' => $channel,
                'status' => 'pending',
                'paid_at' => null,
                'xendit_payment_request_id' => $requestId,
                'payment_stage' => $stage,
                'payment_instruction' => $instructions,
                'external_id' => $result['reference_id'] ?? null,
                'payment_url' => collect($result['actions'] ?? [])
                    ->firstWhere('type', 'REDIRECT_CUSTOMER')['value'] ?? null,
            ]);
        });

        return redirect()->route('pembayaran.instruksi', [
            'bookingCode' => $booking->booking_code,
            'stage' => $stage,
        ]);
    }

    public function instruksiPembayaran(
        Request $request,
        string $bookingCode
    ): View|RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        $stage = $request->query('stage', 'dp');
        abort_unless(in_array($stage, ['dp', 'pelunasan'], true), 404);

        $transaction = $booking->transactions()
            ->where('payment_stage', $stage)
            ->latest('id')
            ->firstOrFail();

        return view(
            'components.pembayaran.instruksi-pembayaran',
            compact('booking', 'transaction', 'stage')
        );
    }

    public function cekStatusPembayaran(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ): RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        $validated = $request->validate([
            'transaction_id' => ['required', 'integer'],
        ]);

        $transaction = $booking->transactions()
            ->whereKey($validated['transaction_id'])
            ->firstOrFail();

        if (!$transaction->xendit_payment_request_id) {
            return back()->with(
                'error',
                'Transaksi belum mempunyai Payment Request Xendit.'
            );
        }

        try {
            $remote = $xendit->getPaymentRequest(
                $transaction->xendit_payment_request_id
            );
        } catch (Throwable $e) {
            report($e);
            Log::error('Gagal mengambil status Xendit.', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Belum berhasil mengambil status terbaru dari Xendit.'
            );
        }

        $remoteRequestId = $remote['payment_request_id']
            ?? $remote['id']
            ?? null;

        if (
            $remoteRequestId !== $transaction->xendit_payment_request_id
            || (int) ($remote['request_amount'] ?? 0) !== (int) $transaction->amount
        ) {
            return back()->with(
                'error',
                'Data pembayaran dari Xendit tidak cocok dengan transaksi.'
            );
        }

        $status = strtoupper($remote['status'] ?? '');

        DB::transaction(function () use ($booking, $transaction, $status) {
            $lockedTransaction = Transaction::whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedTransaction->status, self::PAID_STATUSES, true)) {
                return;
            }

            if ($status === self::XENDIT_SUCCEEDED) {
                $lockedTransaction->status = 'paid';
                $lockedTransaction->paid_at = now();
                $lockedTransaction->save();

                $paidStages = $booking->transactions()
                    ->whereIn('status', self::PAID_STATUSES)
                    ->pluck('payment_stage')
                    ->map(fn ($stage) => strtolower((string) $stage));

                $dpPaid = $paidStages->contains('dp');
                $pelunasanPaid = $paidStages->contains('pelunasan');

                if ($dpPaid && $pelunasanPaid) {
                    $booking->payment_status = 'paid';
                    $booking->booking_status = 'confirmed';
                } elseif ($dpPaid) {
                    $booking->payment_status = 'partial';
                    $booking->booking_status = 'confirmed';
                } else {
                    $booking->payment_status = 'pending';
                }

                $booking->save();
            } elseif (in_array($status, ['FAILED', 'EXPIRED', 'CANCELED'], true)) {
                $lockedTransaction->status = strtolower($status);
                $lockedTransaction->save();
            }
        });

        if ($status === self::XENDIT_SUCCEEDED) {
            return redirect()
                ->route('customer.bookings.index')
                ->with('success', 'Pembayaran berhasil dikonfirmasi oleh Xendit.');
        }

        return back()->with(
            'status_message',
            'Status pembayaran dari Xendit: ' . ($status ?: 'BELUM DIKETAHUI')
        );
    }

    public function cekStatusOtomatis(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ) {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        $transaction = $booking->transactions()
            ->whereIn('payment_stage', ['dp', 'pelunasan'])
            ->whereNotNull('xendit_payment_request_id')
            ->latest('id')
            ->first();

        if (!$transaction || !$transaction->xendit_payment_request_id) {
            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => 'Transaksi belum ditemukan.',
            ]);
        }

        if (in_array(
            strtolower((string) $transaction->status),
            self::PAID_STATUSES,
            true
        )) {
            return response()->json([
                'success' => true,
                'status' => 'paid',
                'payment_status' => $booking->payment_status,
            ]);
        }

        try {
            $remote = $xendit->getPaymentRequest(
                $transaction->xendit_payment_request_id
            );

            Log::info('Pemeriksaan status pembayaran Xendit.', [
                'booking_code' => $bookingCode,
                'transaction_id' => $transaction->id,
                'local_payment_request_id' => $transaction->xendit_payment_request_id,
                'remote_payment_request_id' => $remote['payment_request_id'] ?? $remote['id'] ?? null,
                'local_amount' => (int) $transaction->amount,
                'remote_amount' => $remote['request_amount'] ?? null,
                'remote_status' => $remote['status'] ?? null,
            ]);

            $remoteId = $remote['payment_request_id']
                ?? $remote['id']
                ?? null;

            if (
                $remoteId !== $transaction->xendit_payment_request_id
                || (int) ($remote['request_amount'] ?? 0)
                    !== (int) $transaction->amount
            ) {
                return response()->json([
                    'success' => false,
                    'status' => 'pending',
                    'message' => 'Data transaksi tidak cocok.',
                ], 422);
            }

            $remoteStatus = strtoupper((string) ($remote['status'] ?? ''));

            DB::transaction(function () use (
                $booking,
                $transaction,
                $remoteStatus
            ) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (in_array(
                    strtolower((string) $lockedTransaction->status),
                    self::PAID_STATUSES,
                    true
                )) {
                    return;
                }

                if ($remoteStatus === self::XENDIT_SUCCEEDED) {
                    $lockedTransaction->status = 'paid';
                    $lockedTransaction->paid_at = now();
                    $lockedTransaction->save();

                    $paidStages = $booking->transactions()
                        ->whereIn('status', self::PAID_STATUSES)
                        ->pluck('payment_stage')
                        ->map(fn ($stage) => strtolower((string) $stage));

                    $dpPaid = $paidStages->contains('dp');
                    $pelunasanPaid = $paidStages->contains('pelunasan');

                    if ($dpPaid && $pelunasanPaid) {
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'confirmed';
                    } elseif ($dpPaid) {
                        $booking->payment_status = 'partial';
                        $booking->booking_status = 'confirmed';
                    } else {
                        $booking->payment_status = 'pending';
                    }

                    $booking->save();
                } elseif (in_array(
                    $remoteStatus,
                    ['FAILED', 'EXPIRED', 'CANCELED'],
                    true
                )) {
                    $lockedTransaction->status = strtolower($remoteStatus);
                    $lockedTransaction->save();
                }
            });

            $transaction->refresh();
            $booking->refresh();

            $isPaid = in_array(
                strtolower((string) $transaction->status),
                self::PAID_STATUSES,
                true
            );

            return response()->json([
                'success' => $isPaid,
                'status' => $isPaid
                    ? 'paid'
                    : strtolower($remoteStatus ?: 'pending'),
                'payment_status' => $booking->payment_status,
            ]);
        } catch (Throwable $e) {
            Log::error('Pemeriksaan otomatis pembayaran gagal.', [
                'booking_code' => $bookingCode,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => 'Status pembayaran belum berhasil diperiksa.',
            ], 503);
        }
    }

    private function findOwnedBooking(Request $request, string $bookingCode, bool $fail = true): ?Booking
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $query = Booking::query()
            ->where('booking_code', $bookingCode)
            ->where(function ($builder) use ($user): void {
                $builder->where('user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user): void {
                        $legacy->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            });

        return $fail ? $query->firstOrFail() : $query->first();
    }

    private function bookingRedirect(string $type, string $message): RedirectResponse
    {
        return redirect()->route('customer.bookings.index')->with($type, $message);
    }
}

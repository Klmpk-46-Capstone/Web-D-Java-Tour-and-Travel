<?php

namespace App\Http\Controllers\Pembayaran;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class XenditWebhookController extends Controller
{
    /**
     * Memproses notifikasi pembayaran dari Xendit.
     */
    public function handle(Request $request): JsonResponse
    {
        $callbackToken = (string) config(
            'services.xendit.callback_token'
        );

        $receivedToken = (string) $request->header(
            'x-callback-token',
            ''
        );

        if (
            $callbackToken === ''
            || $receivedToken === ''
            || ! hash_equals($callbackToken, $receivedToken)
        ) {
            Log::warning('Callback Xendit ditolak karena token tidak valid.');

            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $payload = $request->validate([
            'id' => ['required', 'string', 'max:150'],
            'external_id' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:PAID,SETTLED,EXPIRED'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($payload) {
                $transaction = Transaction::query()
                    ->where('external_id', $payload['external_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    Log::warning('Transaksi callback Xendit tidak ditemukan.', [
                        'external_id' => $payload['external_id'],
                    ]);

                    abort(404, 'Transaction not found.');
                }

                /*
                 * Invoice harus cocok dengan transaksi yang tersimpan.
                 * Callback tidak boleh mengganti invoice milik transaksi lain.
                 */
                if (
                    ! filled($transaction->xendit_invoice_id)
                    || ! hash_equals(
                        (string) $transaction->xendit_invoice_id,
                        (string) $payload['id']
                    )
                ) {
                    Log::warning('ID invoice Xendit tidak cocok.', [
                        'transaction_id' => $transaction->id,
                    ]);

                    abort(422, 'Invoice mismatch.');
                }

                $booking = $transaction->booking()
                    ->lockForUpdate()
                    ->first();

                if (! $booking) {
                    abort(404, 'Booking not found.');
                }

                $expectedAmount = (int) $transaction->amount;

                $reportedAmount = (int) (
                    $payload['paid_amount']
                    ?? $payload['amount']
                    ?? 0
                );

                /*
                 * Jangan menandai transaksi berhasil jika nominal
                 * yang dilaporkan tidak sesuai dengan nominal tagihan.
                 */
                if ($payload['status'] === 'PAID'
                    || $payload['status'] === 'SETTLED') {
                    if (
                        $expectedAmount < 1
                        || $reportedAmount !== $expectedAmount
                    ) {
                        Log::warning('Nominal callback Xendit tidak cocok.', [
                            'transaction_id' => $transaction->id,
                            'expected_amount' => $expectedAmount,
                            'reported_amount' => $reportedAmount,
                        ]);

                        abort(422, 'Payment amount mismatch.');
                    }
                }

                /*
                 * Callback dapat dikirim berulang kali.
                 * Transaksi yang sudah berhasil tidak diproses ulang.
                 */
                if (in_array($transaction->status, [
                    'paid',
                    'settled',
                    'completed',
                ], true)) {
                    return;
                }

                if (in_array($booking->booking_status, [
                    'cancelled',
                    'refund_requested',
                ], true)) {
                    Log::warning(
                        'Pembayaran diterima untuk booking yang dibatalkan atau refund.',
                        [
                            'booking_id' => $booking->id,
                            'transaction_id' => $transaction->id,
                        ]
                    );

                    return;
                }

                if (in_array($payload['status'], ['PAID', 'SETTLED'], true)) {
                    $transaction->forceFill([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ])->save();

                    /*
                     * DP sudah dibayar, tetapi sisa pembayaran belum lunas.
                     */
                    $booking->forceFill([
                        'payment_status' => $expectedAmount
                            >= (int) $booking->total_price
                                ? 'paid'
                                : 'partial',
                        'booking_status' => 'confirmed',
                    ])->save();

                    return;
                }

                if ($payload['status'] === 'EXPIRED') {
                    $transaction->forceFill([
                        'status' => 'expired',
                    ])->save();
                }
            });

            return response()->json([
                'message' => 'Webhook processed.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Gagal memproses callback Xendit.', [
                'external_id' => $payload['external_id'] ?? null,
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'Webhook processing failed.',
            ], 500);
        }
    }
}

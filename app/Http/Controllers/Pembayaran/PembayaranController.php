<?php

namespace App\Http\Controllers\Pembayaran;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Services\XenditPaymentService;
use Illuminate\Http\JsonResponse;
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

    /**
     * Menampilkan halaman pemilihan metode pembayaran.
     */
    public function mulaiPembayaran(
        Request $request,
        string $bookingCode
    ): View|RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);
        $booking->load('transactions');

        if ($this->isBookingClosed($booking)) {
            return $this->bookingRedirect('error', 'Booking ini tidak dapat dibayar.');
        }

        $stage = $this->determinePaymentStage($booking);
        if (!$stage) {
            return $this->bookingRedirect('success', 'Booking ini sudah lunas.');
        }

        // Jika instruksi aktif sudah ada dan pengguna tidak meminta ganti metode, arahkan langsung ke instruksi.
        $pendingTransaction = $booking->transactions()
            ->where('payment_stage', $stage)
            ->whereNotNull('xendit_payment_request_id')
            ->whereNotIn('status', array_merge(self::PAID_STATUSES, ['cancelled', 'canceled']))
            ->latest('id')
            ->first();

        if ($pendingTransaction && !$request->boolean('ganti_metode')) {
            return redirect()->route('pembayaran.instruksi', [
                'bookingCode' => $booking->booking_code,
                'stage' => $stage,
            ]);
        }

        $amount = $this->calculateBillAmount($booking, $stage);

        return view('components.pembayaran.metode-pembayaran', compact('booking', 'stage', 'amount'));
    }

    /**
     * Membuat pembayaran baru melalui Xendit.
     */
    public function buatPembayaran(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ): RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        if ($this->isBookingClosed($booking)) {
            return back()->with('error', 'Booking ini tidak dapat dibayar.');
        }

        $validated = $request->validate([
            'channel_code' => ['required', 'string', 'max:50'],
        ]);

        $stage = $this->determinePaymentStage($booking);
        if (!$stage) {
            return $this->bookingRedirect('success', 'Booking ini sudah lunas.');
        }

        $channel = strtoupper(trim($validated['channel_code']));
        $availableChannels = $this->getAvailableChannels();

        if (!in_array($channel, $availableChannels, true)) {
            return back()->withErrors([
                'channel_code' => 'Metode pembayaran ini belum diaktifkan.',
            ])->withInput();
        }

        if ($channel === 'CARDS') {
            return back()->with(
                'error',
                'Pembayaran kartu belum diaktifkan. Konfigurasikan alur kartu dan autentikasi terlebih dahulu.'
            );
        }

        if (str_starts_with($channel, 'DIRECT_DEBIT_') || str_starts_with($channel, 'DD_')) {
            return back()->with(
                'error',
                'Direct Debit memerlukan proses penautan akun dan token pembayaran terlebih dahulu.'
            );
        }

        $amount = $this->calculateBillAmount($booking, $stage);
        if ($amount < 1) {
            return back()->with('error', 'Nominal pembayaran tidak valid.');
        }

        // Periksa apakah tahap ini sudah dibayar sebelumnya
        $latest = $booking->transactions()
            ->where('payment_stage', $stage)
            ->latest('id')
            ->first();

        if ($latest && in_array(strtolower((string) $latest->status), self::PAID_STATUSES, true)) {
            return $this->bookingRedirect('success', 'Tahap pembayaran ini sudah berhasil dibayar.');
        }

        // Batalkan transaksi pending terdahulu jika pengguna mengganti channel
        $existingPending = $booking->transactions()
            ->where('payment_stage', $stage)
            ->whereNotNull('xendit_payment_request_id')
            ->whereIn('status', ['pending', 'requires_action'])
            ->latest('id')
            ->first();

        if ($existingPending) {
            if (strtoupper((string) $existingPending->payment_channel) === $channel) {
                return redirect()->route('pembayaran.instruksi', [
                    'bookingCode' => $booking->booking_code,
                    'stage' => $stage,
                ]);
            }

            $existingPending->forceFill(['status' => 'cancelled'])->save();
        }

        $customerPhone = $booking->customer_phone
            ?? $booking->phone_number
            ?? $booking->phone
            ?? null;

        $channelProperties = $this->buildChannelProperties($channel, $booking, $customerPhone);
        if ($channel === 'OVO' && empty($channelProperties['account_mobile_number'])) {
            return back()->withInput()->with('error', 'Nomor HP pelanggan diperlukan untuk pembayaran OVO.');
        }

        $isRetailOutlet = in_array($channel, ['ALFAMART', 'INDOMARET'], true);

        $payload = [
            'reference_id' => $booking->booking_code . '-' . strtoupper($stage) . '-' . uniqid(),
            'type' => $isRetailOutlet ? 'REUSABLE_PAYMENT_CODE' : 'PAY',
            'country' => 'ID',
            'currency' => 'IDR',
            'request_amount' => $amount,
            'channel_code' => $channel,
            'channel_properties' => $channelProperties,
            'description' => ($stage === 'dp' ? 'DP 30%' : 'Pelunasan 70%') . ' booking ' . $booking->booking_code,
            'metadata' => [
                'booking_code' => $booking->booking_code,
                'payment_stage' => $stage,
            ],
        ];

        try {
            $result = $xendit->createPaymentRequest($payload);
        } catch (Throwable $e) {
            report($e);
            Log::error('Xendit gagal membuat payment request.', [
                'booking_id' => $booking->id,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat pembayaran. Periksa channel dan konfigurasi Xendit.');
        }

        $requestId = $result['payment_request_id'] ?? $result['id'] ?? null;
        if (!$requestId) {
            return back()->with('error', 'Xendit tidak mengembalikan ID pembayaran.');
        }

        $actions = $result['actions'] ?? [];
        $redirectAction = collect($actions)->first(
            fn ($a) => ($a['type'] ?? '') === 'REDIRECT_CUSTOMER' && !empty($a['value'])
        );
        $qrAction = collect($actions)->first(
            fn ($a) => ($a['type'] ?? '') === 'PRESENT_TO_CUSTOMER'
                && ($a['descriptor'] ?? '') === 'QR_STRING'
                && !empty($a['value'])
        );

        $qrString = data_get($result, 'channel_properties.qr_string')
            ?? data_get($result, 'payment_method.qr_code.channel_properties.qr_string')
            ?? ($qrAction['value'] ?? null);

        $isQrPlaceholder = $channel === 'QRIS'
            && (!is_string($qrString) || trim($qrString) === '' || trim($qrString) === 'some-random-qr-string');

        if ($isQrPlaceholder && !$xendit->isDevelopmentMode()) {
            return back()->with(
                'error',
                'Kode QRIS asli tidak diterima dari Xendit. Silakan coba lagi atau hubungi layanan pelanggan.'
            );
        }

        if ($isQrPlaceholder) {
            $qrString = null;
        }

        $expiresAt = data_get($result, 'channel_properties.expires_at');
        if (!$expiresAt) {
            $expiresAt = match (true) {
                $channel === 'QRIS' => now()->addHours(24)->utc()->toIso8601String(),
                $channel === 'OVO' => now()->addMinutes(15)->utc()->toIso8601String(),
                str_ends_with($channel, '_VIRTUAL_ACCOUNT') => now()->addHours(24)->utc()->toIso8601String(),
                default => null,
            };
        }

        $instructions = [
            'status' => $result['status'] ?? 'REQUIRES_ACTION',
            'actions' => $actions,
            'channel_code' => $channel,
            'reference_id' => $result['reference_id'] ?? null,
            'channel_properties' => $result['channel_properties'] ?? [],
            'expires_at' => $expiresAt,
            'qr_string' => $qrString,
            'test_mode' => $xendit->isDevelopmentMode(),
            'qr_placeholder' => $isQrPlaceholder,
        ];

        DB::transaction(function () use (
            $booking,
            $stage,
            $amount,
            $channel,
            $requestId,
            $result,
            $instructions,
            $redirectAction
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
                'payment_url' => $redirectAction['value'] ?? null,
            ]);
        });

        return redirect()->route('pembayaran.instruksi', [
            'bookingCode' => $booking->booking_code,
            'stage' => $stage,
        ]);
    }

    /**
     * Alias method untuk route pembayaran.xendit
     */
    public function buatInvoice(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ): RedirectResponse {
        return $this->buatPembayaran($request, $bookingCode, $xendit);
    }

    /**
     * Menampilkan instruksi pembayaran kepada pengguna.
     */
    public function instruksiPembayaran(
        Request $request,
        string $bookingCode
    ): View|RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        $stage = $request->query('stage', 'dp');
        abort_unless(in_array($stage, ['dp', 'pelunasan'], true), 404);

        $transaction = $booking->transactions()
            ->where('payment_stage', $stage)
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->latest('id')
            ->firstOrFail();

        return view('components.pembayaran.instruksi-pembayaran', compact('booking', 'transaction', 'stage'));
    }

    /**
     * Memeriksa status pembayaran secara manual via tombol pengguna.
     */
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
            return back()->with('error', 'Transaksi belum mempunyai Payment Request Xendit.');
        }

        try {
            $remote = $xendit->getPaymentRequest($transaction->xendit_payment_request_id);
        } catch (Throwable $e) {
            report($e);
            Log::error('Gagal mengambil status Xendit.', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Belum berhasil mengambil status terbaru dari Xendit.');
        }

        $remoteRequestId = $remote['payment_request_id'] ?? $remote['id'] ?? null;
        if (
            $remoteRequestId !== $transaction->xendit_payment_request_id
            || (int) ($remote['request_amount'] ?? 0) !== (int) $transaction->amount
        ) {
            return back()->with('error', 'Data pembayaran dari Xendit tidak cocok dengan transaksi.');
        }

        $status = strtoupper((string) ($remote['status'] ?? ''));
        $this->syncPaymentStatus($booking, $transaction, $status);

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

    /**
     * Endpoint polling status pembayaran otomatis dari JavaScript di halaman instruksi.
     */
    public function cekStatusOtomatis(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ): JsonResponse {
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

        if (in_array(strtolower((string) $transaction->status), self::PAID_STATUSES, true)) {
            return response()->json([
                'success' => true,
                'status' => 'paid',
                'payment_status' => $booking->payment_status,
            ]);
        }

        try {
            $remote = $xendit->getPaymentRequest($transaction->xendit_payment_request_id);

            $remoteId = $remote['payment_request_id'] ?? $remote['id'] ?? null;
            if (
                $remoteId !== $transaction->xendit_payment_request_id
                || (int) ($remote['request_amount'] ?? 0) !== (int) $transaction->amount
            ) {
                return response()->json([
                    'success' => false,
                    'status' => 'pending',
                    'message' => 'Data transaksi tidak cocok.',
                ], 422);
            }

            $remoteStatus = strtoupper((string) ($remote['status'] ?? ''));
            $this->syncPaymentStatus($booking, $transaction, $remoteStatus);

            $transaction->refresh();
            $booking->refresh();

            $isPaid = in_array(strtolower((string) $transaction->status), self::PAID_STATUSES, true);

            return response()->json([
                'success' => $isPaid,
                'status' => $isPaid ? 'paid' : strtolower($remoteStatus ?: 'pending'),
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

    /**
     * Memulai simulasi untuk transaksi QRIS pada Development Mode.
     */
    public function simulasikanPembayaran(
        Request $request,
        string $bookingCode,
        XenditPaymentService $xendit
    ): RedirectResponse {
        abort_unless($xendit->isDevelopmentMode(), 404);

        $booking = $this->findOwnedBooking($request, $bookingCode);
        $validated = $request->validate([
            'transaction_id' => ['required', 'integer', 'min:1'],
        ]);

        $transaction = $booking->transactions()
            ->whereKey($validated['transaction_id'])
            ->whereNotNull('xendit_payment_request_id')
            ->firstOrFail();

        if (strtoupper((string) $transaction->payment_channel) !== 'QRIS') {
            return back()->with('error', 'Simulasi melalui fitur ini hanya tersedia untuk transaksi QRIS.');
        }

        if (in_array(strtolower((string) $transaction->status), self::PAID_STATUSES, true)) {
            return back()->with('success', 'Transaksi ini sudah dibayar.');
        }

        try {
            $result = $xendit->simulatePaymentRequest(
                (string) $transaction->xendit_payment_request_id,
                (int) $transaction->amount
            );

            return back()->with(
                'status_message',
                $result['message'] ?? 'Simulasi pembayaran dimulai. Tunggu konfirmasi status dari Xendit.'
            );
        } catch (Throwable $e) {
            Log::error('Simulasi pembayaran gagal.', [
                'booking_code' => $bookingCode,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Simulasi pembayaran gagal dimulai. Periksa konfigurasi Xendit Development Mode.');
        }
    }

    /**
     * Halaman pengalihan sukses setelah pembayaran.
     */
    public function success(): RedirectResponse
    {
        return redirect()->route('customer.bookings.index')->with('success', 'Pembayaran berhasil diproses.');
    }

    /**
     * Halaman pengalihan pembatalan/kegagalan pembayaran.
     */
    public function failure(): RedirectResponse
    {
        return redirect()->route('customer.bookings.index')->with('error', 'Pembayaran dibatalkan atau gagal.');
    }

    // --------------------------------------------------------------------------
    // PRIVATE REUSABLE HELPER METHODS (Clean Code & DRY)
    // --------------------------------------------------------------------------

    /**
     * Memperbarui status transaksi dan status booking secara aman dan terkunci (row-level locking).
     */
    private function syncPaymentStatus(
        Booking $booking,
        Transaction $transaction,
        string $remoteStatus
    ): void {
        DB::transaction(function () use ($booking, $transaction, $remoteStatus) {
            $lockedTransaction = Transaction::whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array(strtolower((string) $lockedTransaction->status), self::PAID_STATUSES, true)) {
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
            } elseif (in_array($remoteStatus, ['FAILED', 'EXPIRED', 'CANCELED'], true)) {
                $lockedTransaction->status = strtolower($remoteStatus);
                $lockedTransaction->save();
            }
        });
    }

    /**
     * Menentukan tahap pembayaran berikutnya ('dp', 'pelunasan', atau null jika sudah lunas).
     */
    private function determinePaymentStage(Booking $booking): ?string
    {
        $paidStages = $booking->transactions()
            ->whereIn('status', self::PAID_STATUSES)
            ->pluck('payment_stage')
            ->map(fn ($s) => strtolower((string) $s))
            ->unique();

        $dpPaid = $paidStages->contains('dp');
        $pelunasanPaid = $paidStages->contains('pelunasan');

        if ($dpPaid && $pelunasanPaid) {
            return null;
        }

        return $dpPaid ? 'pelunasan' : 'dp';
    }

    /**
     * Menghitung nominal tagihan untuk tahap yang ditentukan.
     */
    private function calculateBillAmount(Booking $booking, string $stage): int
    {
        $totalPrice = (int) $booking->total_price;

        if ($stage === 'dp') {
            return (int) round($totalPrice * 0.30);
        }

        $paidSum = (int) $booking->transactions()
            ->whereIn('status', self::PAID_STATUSES)
            ->sum('amount');

        return max(0, $totalPrice - $paidSum);
    }

    /**
     * Memeriksa apakah booking berada dalam status tertutup/tidak dapat dibayar.
     */
    private function isBookingClosed(Booking $booking): bool
    {
        return in_array(
            strtolower((string) $booking->booking_status),
            ['cancelled', 'canceled', 'refund_requested', 'completed', 'finished'],
            true
        );
    }

    /**
     * Mengambil seluruh kode channel pembayaran yang terdaftar di konfigurasi.
     */
    private function getAvailableChannels(): array
    {
        $channels = [];
        foreach (config('payment_methods.groups', []) as $group) {
            foreach (($group['methods'] ?? []) as $code => $label) {
                $channels[] = strtoupper((string) $code);
            }
        }

        return $channels;
    }

    /**
     * Menyusun channel properties Xendit sesuai jenis channel.
     */
    private function buildChannelProperties(string $channel, Booking $booking, ?string $customerPhone): array
    {
        $properties = [];

        if (str_ends_with($channel, '_VIRTUAL_ACCOUNT')) {
            $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', (string) ($booking->customer_name ?: 'Pelanggan DJava'));
            $properties = [
                'display_name' => substr($cleanName, 0, 50),
                'expires_at' => now()->addHours(24)->utc()->toIso8601String(),
            ];
        } elseif (in_array($channel, ['ALFAMART', 'INDOMARET'], true)) {
            $properties = [
                'customer_name' => substr((string) ($booking->customer_name ?: 'Pelanggan DJava'), 0, 255),
                'expires_at' => now()->addHours(48)->utc()->toIso8601String(),
            ];
        } elseif (in_array($channel, ['DANA', 'SHOPEEPAY'], true)) {
            $properties = [
                'success_return_url' => route('customer.bookings.index'),
            ];
        } elseif ($channel === 'OVO') {
            $phone = preg_replace('/[^0-9+]/', '', (string) $customerPhone);
            if ($phone) {
                $properties = [
                    'account_mobile_number' => $phone,
                    'success_return_url' => route('customer.bookings.index'),
                ];
            }
        } elseif ($channel === 'QRIS') {
            $properties = [
                'expires_at' => now()->addHours(24)->utc()->toIso8601String(),
            ];
        }

        return $properties;
    }

    /**
     * Mengambil booking milik user yang sedang terotentikasi untuk mencegah unauthorized access (IDOR).
     */
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

    /**
     * Helper redirect standar dengan flash message.
     */
    private function bookingRedirect(string $type, string $message): RedirectResponse
    {
        return redirect()->route('customer.bookings.index')->with($type, $message);
    }
}

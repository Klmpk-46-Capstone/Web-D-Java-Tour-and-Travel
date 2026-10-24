<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Illuminate\Support\Str;

class XenditPaymentService
{
    private function client()
    {
        $secret = config('services.xendit.secret_key');

        if (empty($secret)) {
            throw new RuntimeException(
                'XENDIT_SECRET_KEY belum diatur di file .env.'
            );
        }

        return Http::withBasicAuth($secret, '')
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'api-version' => config(
                    'services.xendit.api_version',
                    '2024-11-11'
                ),
            ])
            ->timeout(30);
    }

    /** Simulasi hanya boleh dipanggil menggunakan API key Development. */
    public function simulatePaymentRequest(string $id, int $amount): array
    {
        if (!$this->isDevelopmentMode()) {
            throw new RuntimeException(
                'Simulasi pembayaran hanya tersedia pada Xendit Development Mode.'
            );
        }

        if (!preg_match('/^pr-[A-Za-z0-9-]{36}$/', $id)) {
            throw new RuntimeException('ID Payment Request tidak valid.');
        }

        if ($amount < 1) {
            throw new RuntimeException('Nominal simulasi tidak valid.');
        }

        $baseUrl = rtrim(
            config('services.xendit.base_url', 'https://api.xendit.co'),
            '/'
        );

        $response = $this->client()->post(
            $baseUrl . '/v3/payment_requests/' . rawurlencode($id) . '/simulate',
            ['amount' => $amount]
        );

        if ($response->failed()) {
            Log::error('Simulasi pembayaran Xendit gagal.', [
                'http_status' => $response->status(),
                'payment_request_id' => $id,
                'error_code' => $response->json('error_code'),
            ]);

            throw new RuntimeException(
                $response->json('message')
                    ?? 'Xendit gagal memulai simulasi pembayaran.'
            );
        }

        Log::info('Simulasi pembayaran Xendit dimulai.', [
            'payment_request_id' => $id,
            'http_status' => $response->status(),
            'status' => $response->json('status'),
        ]);

        return $response->json();
    }

    public function isDevelopmentMode(): bool
    {
        $secret = (string) config('services.xendit.secret_key');

        return Str::startsWith($secret, 'xnd_development_');
    }

    public function createPaymentRequest(array $payload): array
    {
        $baseUrl = rtrim(
            config('services.xendit.base_url', 'https://api.xendit.co'),
            '/'
        );

        $endpoint = $baseUrl . '/v3/payment_requests';

        $response = $this->client()->post($endpoint, $payload);

        Log::info('Xendit create payment response diagnostic', [
            'http_status' => $response->status(),
            'response_id' => $response->json('payment_request_id')
                ?? $response->json('id'),
            'response_status' => $response->json('status'),
            'actions' => collect($response->json('actions', []))
                ->map(fn ($action) => [
                    'type' => $action['type'] ?? null,
                    'descriptor' => $action['descriptor'] ?? null,
                    'value_length' => isset($action['value'])
                        && is_string($action['value'])
                            ? strlen($action['value'])
                            : null,
                    'is_placeholder' =>
                        ($action['value'] ?? null) === 'some-random-qr-string',
                ])
                ->all(),
        ]);

        if ($response->failed()) {
            Log::error('Xendit create payment request gagal.', [
                'http_status' => $response->status(),
                'error_code' => $response->json('error_code'),
            ]);

            throw new RuntimeException(
                $response->json('message')
                    ?? 'Xendit gagal membuat instruksi pembayaran.'
            );
        }

        return $response->json();
    }

    public function getPaymentRequest(string $id): array
    {
        $baseUrl = rtrim(config('services.xendit.base_url', 'https://api.xendit.co'), '/');
        $endpoint = $baseUrl . '/v3/payment_requests/' . rawurlencode($id);

        $response = $this->client()->get($endpoint);

        if ($response->failed()) {
            Log::error('Xendit get payment request gagal.', [
                'http_status' => $response->status(),
                'error_code' => $response->json('error_code'),
            ]);

            throw new RuntimeException(
                'Status pembayaran belum dapat diperiksa. Coba lagi.'
            );
        }

        return $response->json();
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

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

    public function createPaymentRequest(array $payload): array
    {
        $baseUrl = rtrim(config('services.xendit.base_url', 'https://api.xendit.co'), '/');
        $endpoint = $baseUrl . '/v3/payment_requests';

        $response = $this->client()->post($endpoint, $payload);

        if ($response->failed()) {
            report(new RuntimeException(
                'Xendit create payment request gagal: '
                . $response->body()
            ));

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
            report(new RuntimeException(
                'Xendit get payment request gagal: '
                . $response->body()
            ));

            throw new RuntimeException(
                'Status pembayaran belum dapat diperiksa. Coba lagi.'
            );
        }

        return $response->json();
    }
}

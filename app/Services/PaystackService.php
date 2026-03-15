<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.paystack.payment_url') ?? 'https://api.paystack.co';
        $this->secretKey = config('services.paystack.secret_key') ?? '';
    }

    /**
     * Initialize a Paystack transaction.
     *
     * @param  array<string, mixed>  $metadata
     * @return array{status: bool, authorization_url?: string, reference?: string, access_code?: string}
     */
    public function initializeTransaction(float $amount, string $email, array $metadata = [], ?string $callbackUrl = null): array
    {
        $payload = [
            'amount' => (int) ($amount * 100),
            'email' => $email,
            'metadata' => $metadata,
        ];

        if ($callbackUrl) {
            $payload['callback_url'] = $callbackUrl;
        }

        $response = $this->makeRequest('POST', '/transaction/initialize', $payload);

        if (! $response->successful() || ! $response->json('status')) {
            return ['status' => false];
        }

        $data = $response->json('data');

        return [
            'status' => true,
            'authorization_url' => $data['authorization_url'],
            'reference' => $data['reference'],
            'access_code' => $data['access_code'],
        ];
    }

    /**
     * Verify a Paystack transaction.
     *
     * @return array{status: bool, data?: array<string, mixed>}
     */
    public function verifyTransaction(string $reference): array
    {
        $response = $this->makeRequest('GET', "/transaction/verify/{$reference}");

        if (! $response->successful() || ! $response->json('status')) {
            return ['status' => false];
        }

        return [
            'status' => true,
            'data' => $response->json('data'),
        ];
    }

    /**
     * Charge an authorization (for recurring payments / auto-renewal).
     *
     * @return array{status: bool, data?: array<string, mixed>}
     */
    public function chargeAuthorization(string $authorizationCode, string $email, float $amount): array
    {
        $response = $this->makeRequest('POST', '/transaction/charge_authorization', [
            'authorization_code' => $authorizationCode,
            'email' => $email,
            'amount' => (int) ($amount * 100),
        ]);

        if (! $response->successful() || ! $response->json('status')) {
            return ['status' => false];
        }

        return [
            'status' => true,
            'data' => $response->json('data'),
        ];
    }

    /**
     * Create a Paystack customer.
     *
     * @return array{status: bool, customer_code?: string}
     */
    public function createCustomer(string $email, string $name): array
    {
        $names = explode(' ', $name, 2);
        $response = $this->makeRequest('POST', '/customer', [
            'email' => $email,
            'first_name' => $names[0],
            'last_name' => $names[1] ?? '',
        ]);

        if (! $response->successful() || ! $response->json('status')) {
            return ['status' => false];
        }

        return [
            'status' => true,
            'customer_code' => $response->json('data.customer_code'),
        ];
    }

    /**
     * Verify a Paystack webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.paystack.webhook_secret') ?? config('services.paystack.secret_key') ?? '';
        $computed = hash_hmac('sha512', $payload, $secret);

        return hash_equals($computed, $signature);
    }

    /**
     * Make an HTTP request to Paystack API.
     *
     * @param  array<string, mixed>  $data
     */
    protected function makeRequest(string $method, string $path, array $data = []): Response
    {
        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->secretKey}",
            'Content-Type' => 'application/json',
        ]);

        $url = $this->baseUrl.$path;

        return match (strtoupper($method)) {
            'POST' => $request->post($url, $data),
            'GET' => $request->get($url, $data),
            default => $request->get($url),
        };
    }
}

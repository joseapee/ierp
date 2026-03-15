<?php

namespace Tests\Unit\Services;

use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaystackService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaystackService;
    }

    public function test_initialize_transaction_returns_url(): void
    {
        Http::fake([
            '*/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test123',
                    'reference' => 'ref_test123',
                    'access_code' => 'ac_test123',
                ],
            ]),
        ]);

        $result = $this->service->initializeTransaction(
            15000.00,
            'user@example.com',
            ['plan' => 'starter'],
            'https://example.com/callback'
        );

        $this->assertTrue($result['status']);
        $this->assertEquals('https://checkout.paystack.com/test123', $result['authorization_url']);
        $this->assertEquals('ref_test123', $result['reference']);
    }

    public function test_verify_transaction(): void
    {
        Http::fake([
            '*/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'ref_test123',
                    'amount' => 1500000,
                    'authorization' => [
                        'authorization_code' => 'AUTH_test',
                    ],
                    'customer' => [
                        'customer_code' => 'CUS_test',
                    ],
                ],
            ]),
        ]);

        $result = $this->service->verifyTransaction('ref_test123');

        $this->assertTrue($result['status']);
        $this->assertEquals('success', $result['data']['status']);
        $this->assertEquals('AUTH_test', $result['data']['authorization']['authorization_code']);
    }

    public function test_charge_authorization(): void
    {
        Http::fake([
            '*/transaction/charge_authorization' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => 'ref_renewal_123',
                    'amount' => 1500000,
                ],
            ]),
        ]);

        $result = $this->service->chargeAuthorization('AUTH_test', 'user@example.com', 15000.00);

        $this->assertTrue($result['status']);
        $this->assertEquals('success', $result['data']['status']);
    }

    public function test_verify_webhook_signature(): void
    {
        config(['services.paystack.webhook_secret' => 'test_secret_key']);
        $service = new PaystackService;

        $payload = '{"event":"charge.success","data":{"id":123}}';
        $validSignature = hash_hmac('sha512', $payload, 'test_secret_key');

        $this->assertTrue($service->verifyWebhookSignature($payload, $validSignature));
        $this->assertFalse($service->verifyWebhookSignature($payload, 'invalid_signature'));
    }

    public function test_initialize_transaction_handles_failure(): void
    {
        Http::fake([
            '*/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Invalid key',
            ], 401),
        ]);

        $result = $this->service->initializeTransaction(15000.00, 'user@example.com');

        $this->assertFalse($result['status']);
    }

    public function test_create_customer(): void
    {
        Http::fake([
            '*/customer' => Http::response([
                'status' => true,
                'data' => [
                    'customer_code' => 'CUS_new123',
                ],
            ]),
        ]);

        $result = $this->service->createCustomer('user@example.com', 'John Doe');

        $this->assertTrue($result['status']);
        $this->assertEquals('CUS_new123', $result['customer_code']);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key') ?: '';
    }

    /**
     * Initialize a Paystack transaction.
     * Amount is passed in GHS, which is converted to pesewas (amount * 100).
     */
    public function initializeTransaction(string $email, float $amountGhs, string $reference, string $callbackUrl): ?array
    {
        if (empty($this->secretKey)) {
            Log::error('Paystack secret key is not configured.');
            return null;
        }

        $amountPesewas = (int) round($amountGhs * 100);

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email' => $email,
                    'amount' => $amountPesewas,
                    'reference' => $reference,
                    'callback_url' => $callbackUrl,
                    'metadata' => [
                        'custom_fields' => [
                            [
                                'display_name' => 'Payment Reason',
                                'variable_name' => 'payment_reason',
                                'value' => 'School Invoice Billing',
                            ],
                        ],
                    ],
                ]);

            if ($response->successful() && $response->json('status')) {
                return $response->json('data');
            }

            Log::error('Paystack transaction initialization failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Paystack initialization error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify a transaction via reference code.
     */
    public function verifyTransaction(string $reference): ?array
    {
        if (empty($this->secretKey)) {
            Log::error('Paystack secret key is not configured.');
            return null;
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->get("https://api.paystack.co/transaction/verify/" . urlencode($reference));

            if ($response->successful() && $response->json('status')) {
                return $response->json('data');
            }

            Log::error('Paystack transaction verification failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Paystack verification error: ' . $e->getMessage());
            return null;
        }
    }
}

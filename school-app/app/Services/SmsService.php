<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $apiKey;
    protected string $senderId;
    protected string $baseUrl = 'https://sms.arkesel.com/api/v2/sms/send';

    public function __construct()
    {
        $this->apiKey = config('services.arkesel.api_key') ?? '';
        $this->senderId = config('services.arkesel.sender_id') ?? 'HanaraSchool';
    }

    /**
     * Send a single SMS message.
     */
    public function sendSms(string $to, string $message): bool
    {
        return $this->sendBulkSms([$to], $message);
    }

    /**
     * Send SMS to multiple recipients.
     *
     * @param array $recipients Array of phone numbers
     * @param string $message The message text
     * @return bool Whether the request was successful
     */
    public function sendBulkSms(array $recipients, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('SmsService: ARKESEL_API_KEY is not configured. SMS not sent.');
            return false;
        }

        if (empty($recipients)) {
            return false;
        }

        // Format phone numbers — ensure they start with country code
        $formattedRecipients = array_map(fn($phone) => $this->formatPhoneNumber($phone), $recipients);
        $formattedRecipients = array_filter($formattedRecipients);

        if (empty($formattedRecipients)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
            ])->post($this->baseUrl, [
                'sender' => $this->senderId,
                'message' => $message,
                'recipients' => $formattedRecipients,
            ]);

            if ($response->successful()) {
                Log::info('SmsService: SMS sent successfully', [
                    'recipients_count' => count($formattedRecipients),
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('SmsService: Failed to send SMS', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('SmsService: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Format a Ghanaian phone number to include the country code.
     */
    protected function formatPhoneNumber(string $phone): ?string
    {
        // Remove spaces, dashes, and other formatting
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (empty($phone)) {
            return null;
        }

        // Already has country code
        if (str_starts_with($phone, '+233')) {
            return $phone;
        }

        if (str_starts_with($phone, '233')) {
            return '+' . $phone;
        }

        // Local format: 0xx xxx xxxx → +233 xx xxx xxxx
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+233' . substr($phone, 1);
        }

        // Assume it's already formatted or a different format
        return $phone;
    }
}

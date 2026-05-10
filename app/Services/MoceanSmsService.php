<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoceanSmsService
{
    protected ?string $apiToken;
    protected ?string $sender;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiToken = config('services.mocean.api_token');
        $this->sender = config('services.mocean.sender', 'CLINIQ');
        $this->enabled = (bool) config('services.mocean.enabled');
    }

    public function send(?string $phoneNumber, string $message): bool
    {
        if (!$phoneNumber) {
            Log::warning('Mocean SMS skipped: missing phone number.', [
                'message' => $message,
            ]);

            return false;
        }

        if (!$this->enabled) {
            Log::info('Mocean SMS skipped because MOCEAN_ENABLED=false.', [
                'to' => $phoneNumber,
                'message' => $message,
            ]);

            return false;
        }

        if (!$this->apiToken) {
            Log::warning('Mocean SMS failed: missing API token.');

            return false;
        }

        $formattedPhone = $this->formatPhilippineNumber($phoneNumber);

        if (!$formattedPhone) {
            Log::warning('Mocean SMS failed: invalid phone number.', [
                'original_phone' => $phoneNumber,
            ]);

            return false;
        }

        try {
            Log::info('Mocean SMS request starting.', [
                'to' => $formattedPhone,
                'sender' => $this->sender,
                'message_preview' => mb_substr($message, 0, 80),
            ]);

            $response = Http::asForm()
                ->timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/xml, application/json, text/plain, */*',
                ])
                ->post('https://rest.moceanapi.com/rest/2/sms', [
                    'mocean-from' => $this->sender,
                    'mocean-to' => $formattedPhone,
                    'mocean-text' => $message,
                ]);

            if ($response->successful()) {
                Log::info('Mocean SMS sent.', [
                    'to' => $formattedPhone,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return true;
            }

            Log::warning('Mocean SMS failed.', [
                'to' => $formattedPhone,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Mocean SMS exception.', [
                'to' => $formattedPhone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function formatPhilippineNumber(string $phoneNumber): ?string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', trim($phoneNumber));

        if (!$phone) {
            return null;
        }

        if (preg_match('/^639\d{9}$/', $phone)) {
            return $phone;
        }

        if (preg_match('/^\+639\d{9}$/', $phone)) {
            return substr($phone, 1);
        }

        if (preg_match('/^09\d{9}$/', $phone)) {
            return '63' . substr($phone, 1);
        }

        return null;
    }
}
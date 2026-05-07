<?php

namespace App\Services;

use App\Models\SmsMessageLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SemaphoreSmsService
{
    public function sendToModel(?Model $recipient, string $message, ?Model $smsable = null, array $meta = []): SmsMessageLog
    {
        $number = $recipient ? $this->extractMobileNumber($recipient) : null;

        return $this->send(
            number: $number,
            message: $message,
            smsable: $smsable,
            notifiable: $recipient,
            meta: $meta
        );
    }

    public function send(
        ?string $number,
        string $message,
        ?Model $smsable = null,
        ?Model $notifiable = null,
        array $meta = []
    ): SmsMessageLog {
        $senderName = trim((string) config('semaphore.sender_name', ''));

        $log = SmsMessageLog::create([
            'smsable_type' => $smsable ? $smsable::class : null,
            'smsable_id' => $smsable?->getKey(),
            'notifiable_type' => $notifiable ? $notifiable::class : null,
            'notifiable_id' => $notifiable?->getKey(),
            'provider' => 'semaphore',
            'recipient_number' => $number,
            'sender_name' => $senderName,
            'message' => $message,
            'status' => 'pending',
        ]);

        if (!config('semaphore.enabled')) {
            $log->update([
                'status' => 'skipped',
                'error_message' => 'Semaphore SMS is disabled. Set SEMAPHORE_ENABLED=true to send.',
            ]);

            return $log;
        }

        if (!config('semaphore.api_key')) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Missing SEMAPHORE_API_KEY.',
            ]);

            return $log;
        }

        $normalizedNumber = $this->normalizePhilippineNumber($number);

        if (!$normalizedNumber) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Invalid or missing Philippine mobile number.',
            ]);

            return $log;
        }

        $safeMessage = $this->sanitizeMessage($message);

        try {
            $endpoint = rtrim((string) config('semaphore.base_url'), '/')
                . (config('semaphore.priority') ? '/priority' : '/messages');

            $payload = [
                'apikey' => config('semaphore.api_key'),
                'number' => $normalizedNumber,
                'message' => $safeMessage,
            ];

            if ($senderName !== '') {
                $payload['sendername'] = $senderName;
            }

            $response = Http::asForm()
                ->connectTimeout((int) config('semaphore.connect_timeout', 30))
                ->timeout((int) config('semaphore.timeout', 60))
                ->retry(2, 1500)
                ->post($endpoint, $payload);

            $json = $response->json();

            if (!$response->successful()) {
                $log->update([
                    'status' => 'failed',
                    'recipient_number' => $normalizedNumber,
                    'provider_response' => is_array($json) ? $json : ['raw' => $response->body()],
                    'error_message' => 'Semaphore request failed with HTTP ' . $response->status(),
                ]);

                return $log;
            }

            $firstMessage = is_array($json) && array_is_list($json)
                ? ($json[0] ?? [])
                : (is_array($json) ? $json : []);

            if (isset($firstMessage['message']) && !isset($firstMessage['message_id'])) {
                $log->update([
                    'status' => 'failed',
                    'recipient_number' => $normalizedNumber,
                    'provider_response' => is_array($json) ? $json : ['raw' => $response->body()],
                    'error_message' => (string) $firstMessage['message'],
                ]);

                return $log;
            }

            $providerStatus = strtolower((string) ($firstMessage['status'] ?? 'sent'));

            $log->update([
                'status' => in_array($providerStatus, ['queued', 'pending', 'sent'], true)
                    ? $providerStatus
                    : 'sent',
                'recipient_number' => $normalizedNumber,
                'provider_message_id' => $firstMessage['message_id'] ?? null,
                'provider_response' => is_array($json) ? $json : ['raw' => $response->body()],
                'sent_at' => now(),
            ]);

            return $log;
        } catch (Throwable $e) {
            Log::error('Semaphore SMS failed', [
                'message' => $e->getMessage(),
                'number' => $number,
                'smsable' => $smsable ? $smsable::class . ':' . $smsable->getKey() : null,
                'meta' => $meta,
            ]);

            $log->update([
                'status' => 'failed',
                'recipient_number' => $normalizedNumber,
                'error_message' => $e->getMessage(),
            ]);

            return $log;
        }
    }

    public function extractMobileNumber(Model $model): ?string
    {
        $possibleColumns = [
            'phone_number',
            'mobile_number',
            'contact_number',
            'phone',
            'mobile',
            'contact',
        ];

        foreach ($possibleColumns as $column) {
            $value = data_get($model, $column);

            if (!empty($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    public function normalizePhilippineNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $clean = preg_replace('/[^\d+]/', '', $number);

        if (str_starts_with($clean, '+63')) {
            $clean = '63' . substr($clean, 3);
        }

        if (str_starts_with($clean, '09') && strlen($clean) === 11) {
            return '63' . substr($clean, 1);
        }

        if (str_starts_with($clean, '9') && strlen($clean) === 10) {
            return '63' . $clean;
        }

        if (str_starts_with($clean, '63') && strlen($clean) === 12) {
            return $clean;
        }

        return null;
    }

    private function sanitizeMessage(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message));

        if (preg_match('/^test\b/i', $message)) {
            $message = 'CliniQ: ' . $message;
        }

        return $message;
    }
}
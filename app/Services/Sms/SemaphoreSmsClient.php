<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;

/**
 * Sends SMS through Semaphore (Philippines).
 *
 * @see https://semaphore.co/docs
 */
class SemaphoreSmsClient implements SmsClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $senderName,
        private readonly string $baseUrl,
    ) {}

    public function send(string $to, string $message): void
    {
        Http::asForm()
            ->timeout(15)
            ->post(rtrim($this->baseUrl, '/').'/messages', [
                'apikey' => $this->apiKey,
                'number' => preg_replace('/\D+/', '', $to),
                'message' => $message,
                'sendername' => $this->senderName,
            ])
            ->throw();
    }
}

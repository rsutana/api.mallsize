<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Writes SMS messages to the application log instead of sending them.
 */
class LogSmsClient implements SmsClient
{
    public function send(string $to, string $message): void
    {
        Log::info('[SMS] to '.$to.': '.$message);
    }
}

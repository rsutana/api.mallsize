<?php

namespace App\Services;

use App\Models\NotificationLog;

class NotificationLogService
{
    public function record(string $title, int $recipients = 1, string $channels = 'Email + SMS'): NotificationLog
    {
        return NotificationLog::query()->create([
            'title' => $title,
            'channels' => $channels,
            'recipients_count' => $recipients,
            'sent_at' => now(),
        ]);
    }
}

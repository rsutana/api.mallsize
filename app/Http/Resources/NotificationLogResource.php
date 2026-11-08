<?php

namespace App\Http\Resources;

use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NotificationLog
 */
class NotificationLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'channels' => $this->channels,
            'recipients_count' => $this->recipients_count,
            'sent_at' => $this->sent_at->toIso8601String(),
        ];
    }
}

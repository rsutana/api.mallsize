<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Back-office feed of notifications sent to students.
 *
 * @property int $id
 * @property string $title
 * @property string $channels
 * @property int $recipients_count
 * @property CarbonImmutable $sent_at
 */
#[Fillable(['title', 'channels', 'recipients_count', 'sent_at'])]
class NotificationLog extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipients_count' => 'integer',
            'sent_at' => 'datetime',
        ];
    }
}

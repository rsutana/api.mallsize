<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email + SMS notification that respects the user's notification settings.
 */
abstract class StudentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        $channels = [];

        if ($notifiable->is_email_notifications) {
            $channels[] = 'mail';
        }

        if ($notifiable->is_sms_notifications && filled($notifiable->mobile)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    abstract public function toMail(User $notifiable): MailMessage;

    abstract public function toSms(User $notifiable): string;
}

<?php

namespace App\Notifications\Channels;

use App\Services\Sms\SmsClient;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(private readonly SmsClient $client) {}

    public function send(object $notifiable, Notification $notification): void
    {
        $to = method_exists($notifiable, 'routeNotificationFor') ? $notifiable->routeNotificationFor('sms', $notification) : null;

        if (! is_string($to) || $to === '' || ! method_exists($notification, 'toSms')) {
            return;
        }

        $this->client->send($to, (string) $notification->toSms($notifiable));
    }
}

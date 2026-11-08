<?php

namespace App\Notifications;

use App\Models\Orientation;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class OrientationPublishedNotification extends StudentNotification
{
    public function __construct(public Orientation $orientation) {}

    public function toMail(User $notifiable): MailMessage
    {
        $o = $this->orientation;

        $mail = (new MailMessage)
            ->subject('Orientation: '.$o->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line($o->title)
            ->line($o->date->format('l, F j, Y').($o->time_label ? ' · '.$o->time_label : '').($o->venue ? ' · '.$o->venue : ''));

        if ($o->details) {
            $mail->line($o->details);
        }

        if ($o->is_social_linked && $o->social_url) {
            $mail->action('View the announcement', $o->social_url);
        }

        return $mail;
    }

    public function toSms(User $notifiable): string
    {
        $o = $this->orientation;

        return 'MallSize: '.$o->title.' on '.$o->date->format('M j').($o->time_label ? ', '.$o->time_label : '').($o->venue ? ' at '.$o->venue : '').'.';
    }
}

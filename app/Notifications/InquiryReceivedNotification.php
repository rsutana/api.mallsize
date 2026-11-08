<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Forwards a contact-form inquiry to the staff mailbox.
 */
class InquiryReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Inquiry $inquiry) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Website inquiry from '.$this->inquiry->name)
            ->replyTo($this->inquiry->email, $this->inquiry->name)
            ->line('Name: '.$this->inquiry->name)
            ->line('Email: '.$this->inquiry->email)
            ->line('Mobile: '.($this->inquiry->mobile ?: '—'))
            ->line('Message:')
            ->line($this->inquiry->message);
    }
}

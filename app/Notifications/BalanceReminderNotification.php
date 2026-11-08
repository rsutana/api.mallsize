<?php

namespace App\Notifications;

use App\Models\Enrollment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class BalanceReminderNotification extends StudentNotification
{
    /**
     * @param  Collection<int, Enrollment>  $enrollments
     */
    public function __construct(public Collection $enrollments) {}

    public function toMail(User $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Reminder: remaining balance before your training')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Please settle your remaining balance before your first training day:');

        foreach ($this->enrollments as $enrollment) {
            $mail->line('• '.$enrollment->course->name.': '.Money::format($enrollment->balance()).' — starts '.$enrollment->batchSet->start_date->format('M j, Y'));
        }

        return $mail->action('Pay in my portal', rtrim((string) config('app.frontend_url'), '/').'/portal/payments');
    }

    public function toSms(User $notifiable): string
    {
        $total = $this->enrollments->sum(fn (Enrollment $e): int => $e->balance());

        return 'MallSize: Reminder — you have a remaining balance of '.Money::format($total).'. Please settle it before your training starts.';
    }
}

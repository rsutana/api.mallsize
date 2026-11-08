<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;
use App\Support\Lookups;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceivedNotification extends StudentNotification
{
    public function __construct(public Payment $payment) {}

    public function toMail(User $notifiable): MailMessage
    {
        $payment = $this->payment->loadMissing('allocations.enrollment.course');
        $mail = (new MailMessage)
            ->subject('Payment received — '.$payment->reference_no)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('We received '.Money::format($payment->amount).' via '.Lookups::label('payment_methods', $payment->method).'.')
            ->line('Receipt no. '.$payment->reference_no);

        foreach ($payment->allocations as $allocation) {
            $enrollment = $allocation->enrollment;
            $mail->line('• '.$enrollment->course->name.': '.Money::format($allocation->amount).' paid · remaining '.Money::format($enrollment->balance()));
        }

        return $mail
            ->line('Any remaining balance is payable when your training starts.')
            ->action('Open my portal', rtrim((string) config('app.frontend_url'), '/').'/portal');
    }

    public function toSms(User $notifiable): string
    {
        return 'MallSize: We received '.Money::format($this->payment->amount).' via '
            .Lookups::label('payment_methods', $this->payment->method).'. Receipt '.$this->payment->reference_no.'. Thank you!';
    }
}

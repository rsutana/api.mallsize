<?php

namespace App\Notifications;

use App\Models\Lookups\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent when staff verify or reject a payment.
 */
class PaymentReviewedNotification extends StudentNotification
{
    public function __construct(public Payment $payment) {}

    public function toMail(User $notifiable): MailMessage
    {
        $verified = $this->payment->status === PaymentStatus::VERIFIED;

        $mail = (new MailMessage)
            ->subject(($verified ? 'Payment verified' : 'Payment needs attention').' — '.$this->payment->reference_no)
            ->greeting('Hi '.$notifiable->name.',');

        if ($verified) {
            return $mail->line('Your payment of '.Money::format($this->payment->amount).' (receipt '.$this->payment->reference_no.') has been verified by our staff.');
        }

        return $mail
            ->line('We could not verify your payment of '.Money::format($this->payment->amount).' (receipt '.$this->payment->reference_no.').')
            ->line('Reason: '.($this->payment->rejected_reason ?: 'Please contact our office.'))
            ->line('Please contact our office or make the payment again from your portal.');
    }

    public function toSms(User $notifiable): string
    {
        return $this->payment->status === PaymentStatus::VERIFIED
            ? 'MallSize: Your payment '.$this->payment->reference_no.' ('.Money::format($this->payment->amount).') is verified.'
            : 'MallSize: We could not verify payment '.$this->payment->reference_no.'. Please check your email or contact our office.';
    }
}

<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->isStaff() || ($user->student !== null && $payment->student_id === $user->student->id);
    }
}

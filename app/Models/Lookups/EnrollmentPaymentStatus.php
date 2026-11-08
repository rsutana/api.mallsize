<?php

namespace App\Models\Lookups;

/**
 * Derived payment standing of one enrollment (fee vs. paid). Used for labels and filters.
 */
class EnrollmentPaymentStatus extends LookupModel
{
    protected $table = 'enrollment_payment_statuses';

    public const FULLY_PAID = 'fully_paid';
    public const DOWN_PAYMENT_ONLY = 'down_payment_only';
    public const AWAITING_PAYMENT = 'awaiting_payment';
}

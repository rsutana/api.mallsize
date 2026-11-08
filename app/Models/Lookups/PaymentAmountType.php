<?php

namespace App\Models\Lookups;

/**
 * What a payment allocation covers for its enrollment.
 */
class PaymentAmountType extends LookupModel
{
    protected $table = 'payment_amount_types';

    public const DOWN_PAYMENT = 'down_payment';

    public const FULL = 'full';

    public const BALANCE = 'balance';

    public const PARTIAL = 'partial';
}

<?php

namespace App\Models\Lookups;

/**
 * Payment lifecycle. Paid = confirmed by the gateway; verified = reconciled by staff.
 */
class PaymentStatus extends LookupModel
{
    protected $table = 'payment_statuses';

    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const VERIFIED = 'verified';
    public const FAILED = 'failed';
    public const REJECTED = 'rejected';

    /**
     * Statuses whose amount counts toward an enrollment's paid total.
     */
    public const SETTLED = [self::PAID, self::VERIFIED];
}

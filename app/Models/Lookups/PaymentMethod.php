<?php

namespace App\Models\Lookups;

/**
 * Payment methods. Online ones are offered through the payment gateway.
 */
class PaymentMethod extends LookupModel
{
    protected $table = 'payment_methods';

    public const GCASH = 'gcash';
    public const MAYA = 'maya';
    public const QRPH = 'qrph';
    public const SHOPEEPAY = 'shopeepay';
    public const CARD = 'card';
    public const BANK_TRANSFER = 'bank_transfer';

    /**
     * Methods offered online through the payment gateway.
     */
    public const ONLINE = [self::MAYA, self::QRPH, self::GCASH, self::SHOPEEPAY, self::CARD];
}

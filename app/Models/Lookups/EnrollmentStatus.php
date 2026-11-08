<?php

namespace App\Models\Lookups;

/**
 * Enrollment lifecycle. Pending enrollments hold a slot until paid or expired.
 */
class EnrollmentStatus extends LookupModel
{
    protected $table = 'enrollment_statuses';

    public const PENDING_PAYMENT = 'pending_payment';
    public const ENROLLED = 'enrolled';
    public const CANCELLED = 'cancelled';
    public const EXPIRED = 'expired';

    /**
     * Statuses shown to students and counted as committed.
     */
    public const ACTIVE = [self::ENROLLED, self::PENDING_PAYMENT];
}

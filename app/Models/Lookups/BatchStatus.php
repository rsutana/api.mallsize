<?php

namespace App\Models\Lookups;

/**
 * Batch availability for enrollment.
 */
class BatchStatus extends LookupModel
{
    protected $table = 'batch_statuses';

    public const OPEN = 'open';

    public const FULL = 'full';

    public const CLOSED = 'closed';
}

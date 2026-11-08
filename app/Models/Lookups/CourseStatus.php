<?php

namespace App\Models\Lookups;

/**
 * Course visibility on the public site and for enrollment.
 */
class CourseStatus extends LookupModel
{
    protected $table = 'course_statuses';

    public const OPEN = 'open';

    public const CLOSED = 'closed';

    public const ARCHIVED = 'archived';
}

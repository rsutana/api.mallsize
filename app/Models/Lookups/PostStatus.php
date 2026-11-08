<?php

namespace App\Models\Lookups;

/**
 * Publishing state for news posts and orientations.
 */
class PostStatus extends LookupModel
{
    protected $table = 'post_statuses';

    public const DRAFT = 'draft';
    public const SCHEDULED = 'scheduled';
    public const PUBLISHED = 'published';
}

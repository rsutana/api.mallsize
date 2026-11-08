<?php

namespace App\Models\Lookups;

/**
 * News post categories.
 */
class NewsCategory extends LookupModel
{
    protected $table = 'news_categories';

    public const ANNOUNCEMENT = 'announcement';

    public const ENROLLMENT = 'enrollment';

    public const EVENT = 'event';

    public const GRADUATION = 'graduation';
}

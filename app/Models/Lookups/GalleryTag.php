<?php

namespace App\Models\Lookups;

/**
 * Gallery photo categories.
 */
class GalleryTag extends LookupModel
{
    protected $table = 'gallery_tags';

    public const TEAM = 'team';

    public const TRAINING = 'training';

    public const GRADUATION = 'graduation';

    public const FACILITY = 'facility';
}

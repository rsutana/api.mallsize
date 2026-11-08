<?php

namespace App\Models\Lookups;

/**
 * Social platforms an orientation can link to.
 */
class SocialPlatform extends LookupModel
{
    protected $table = 'social_platforms';

    public const FACEBOOK = 'facebook';

    public const INSTAGRAM = 'instagram';

    public const TIKTOK = 'tiktok';

    public const YOUTUBE = 'youtube';
}

<?php

namespace App\Models\Lookups;

/**
 * How an orientation is held.
 */
class OrientationFormat extends LookupModel
{
    protected $table = 'orientation_formats';

    public const ON_SITE = 'on_site';
    public const ONLINE = 'online';
    public const FACEBOOK_LIVE = 'facebook_live';
}

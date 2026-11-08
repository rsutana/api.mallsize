<?php

namespace App\Models\Lookups;

/**
 * How the public orientation card uses its social link.
 */
class OrientationLinkMode extends LookupModel
{
    protected $table = 'orientation_link_modes';

    public const DIRECT = 'direct';

    public const DETAILS = 'details';
}

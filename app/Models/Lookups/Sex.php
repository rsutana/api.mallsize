<?php

namespace App\Models\Lookups;

/**
 * Sex as recorded on TESDA records.
 */
class Sex extends LookupModel
{
    protected $table = 'sexes';

    public const MALE = 'male';

    public const FEMALE = 'female';
}

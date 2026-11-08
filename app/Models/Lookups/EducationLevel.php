<?php

namespace App\Models\Lookups;

/**
 * Highest educational attainment.
 */
class EducationLevel extends LookupModel
{
    protected $table = 'education_levels';

    public const ELEMENTARY = 'elementary';

    public const HIGH_SCHOOL = 'high_school';

    public const SENIOR_HIGH_SCHOOL = 'senior_high_school';

    public const COLLEGE = 'college';

    public const VOCATIONAL = 'vocational';
}

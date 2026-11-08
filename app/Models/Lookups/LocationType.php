<?php

namespace App\Models\Lookups;

/**
 * Partner meat shop specialisation.
 */
class LocationType extends LookupModel
{
    protected $table = 'location_types';

    public const PORK = 'pork';
    public const BEEF = 'beef';
    public const PORK_AND_BEEF = 'pork_and_beef';
    public const POULTRY = 'poultry';
    public const PROCESSED_MEATS = 'processed_meats';
}

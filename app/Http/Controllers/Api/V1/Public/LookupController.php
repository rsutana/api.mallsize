<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LookupsResource;

class LookupController extends Controller
{
    /**
     * All active option lists (location types, statuses, …) for the SPA.
     */
    public function __invoke(): LookupsResource
    {
        return new LookupsResource(null);
    }
}

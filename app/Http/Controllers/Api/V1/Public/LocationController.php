<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    /**
     * Active partner meat shops students can train at.
     */
    public function index(): AnonymousResourceCollection
    {
        return LocationResource::collection(Location::query()->active()->orderBy('name')->get());
    }
}

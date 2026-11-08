<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrientationResource;
use App\Models\Orientation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrientationController extends Controller
{
    /**
     * Published orientations from today onward.
     */
    public function index(): AnonymousResourceCollection
    {
        return OrientationResource::collection(
            Orientation::query()->published()->whereDate('date', '>=', today())->with('course')->orderBy('date')->get(),
        );
    }
}

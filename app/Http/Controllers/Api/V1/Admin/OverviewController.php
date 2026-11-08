<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminOverviewResource;
use App\Services\AdminOverviewService;

class OverviewController extends Controller
{
    public function __invoke(AdminOverviewService $overview): AdminOverviewResource
    {
        return new AdminOverviewResource($overview->summary());
    }
}

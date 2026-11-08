<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentDashboardResource;
use App\Services\StudentDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StudentDashboardService $dashboard): StudentDashboardResource
    {
        return new StudentDashboardResource($dashboard->summary($request->user()->student));
    }
}

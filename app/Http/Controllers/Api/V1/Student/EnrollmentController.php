<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\StoreStudentEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\Location;
use App\Services\EnrollmentService;
use App\Services\StudentDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request, StudentDashboardService $dashboard): AnonymousResourceCollection
    {
        return EnrollmentResource::collection($dashboard->enrollments($request->user()->student));
    }

    /**
     * Reserve slots in more courses for an existing student.
     */
    public function store(StoreStudentEnrollmentRequest $request, EnrollmentService $enrollments): JsonResponse
    {
        $created = $enrollments->createPending(
            $request->user()->student,
            Location::query()->findOrFail($request->integer('location_id')),
            array_values(array_map('intval', (array) $request->validated('set_ids'))),
        );

        return EnrollmentResource::collection($created->each->load(['course', 'batch', 'batchSet', 'location']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

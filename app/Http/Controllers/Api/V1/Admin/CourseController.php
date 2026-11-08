<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\SaveCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CourseResource::collection(
            Course::query()->ordered()->withCount('enrollments')->with(['batches.sets' => fn ($q) => $q->withSlotsTaken()])->get(),
        );
    }

    public function store(SaveCourseRequest $request, CourseService $courses): JsonResponse
    {
        return (new CourseResource($this->loaded($courses->save($request->validated()))))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Course $course): CourseResource
    {
        return new CourseResource($this->loaded($course));
    }

    public function update(SaveCourseRequest $request, Course $course, CourseService $courses): CourseResource
    {
        return new CourseResource($this->loaded($courses->save($request->validated(), $course)));
    }

    public function destroy(Course $course, CourseService $courses): Response
    {
        $courses->delete($course);

        return response()->noContent();
    }

    private function loaded(Course $course): Course
    {
        return $course->loadCount('enrollments')->load(['batches.sets' => fn ($q) => $q->withSlotsTaken()]);
    }
}

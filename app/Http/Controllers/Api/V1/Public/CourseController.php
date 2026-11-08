<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Lookups\BatchStatus;
use App\Models\Lookups\CourseStatus;
use Closure;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    /**
     * Open courses with their bookable batches and sets.
     */
    public function index(): AnonymousResourceCollection
    {
        return CourseResource::collection(
            Course::query()->open()->ordered()->with($this->relations())->get(),
        );
    }

    public function show(Course $course): CourseResource
    {
        abort_unless($course->status === CourseStatus::OPEN, 404);

        return new CourseResource($course->load($this->relations()));
    }

    /**
     * @return array<string, Closure>
     */
    private function relations(): array
    {
        return [
            'batches' => fn ($q) => $q->where('status', '!=', BatchStatus::CLOSED),
            'batches.sets' => fn ($q) => $q->withSlotsTaken(),
        ];
    }
}

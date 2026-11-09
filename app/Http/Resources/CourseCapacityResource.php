<?php

namespace App\Http\Resources;

use App\Models\Course;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A course with seat usage rolled up from its batches and sets.
 *
 * @mixin Course
 */
class CourseCapacityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'status_label' => Lookups::label('course_statuses', $this->status),
            ...SetCapacityResource::totals($this->batches->flatMap->sets),
            'batches' => BatchCapacityResource::collection($this->batches),
        ];
    }
}

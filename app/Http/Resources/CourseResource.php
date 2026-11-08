<?php

namespace App\Http\Resources;

use App\Models\BatchSet;
use App\Models\Course;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Money fields are centavos.
 *
 * @mixin Course
 */
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sets = $this->relationLoaded('batches')
            ? $this->batches->flatMap(fn ($batch) => $batch->relationLoaded('sets') ? $batch->sets : [])
            : collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'fee' => $this->fee,
            'min_down_payment' => $this->min_down_payment,
            'status' => $this->status,
            'status_label' => Lookups::label('course_statuses', $this->status),
            'sort_order' => $this->sort_order,
            'batches' => BatchResource::collection($this->whenLoaded('batches')),
            'next_start_date' => $this->whenLoaded('batches', fn () => $sets
                ->filter(fn (BatchSet $s) => ! $s->start_date->isPast() || $s->start_date->isToday())
                ->min(fn (BatchSet $s) => $s->start_date->toDateString())),
            'training_days' => $this->whenLoaded('batches', fn () => $sets->sortBy('start_date')->first()?->training_days),
            'total_slots' => $this->whenLoaded('batches', fn () => $sets->sum('slots')),
            'slots_taken' => $this->whenLoaded('batches', fn () => $sets->sum(fn (BatchSet $s) => $s->slotsTaken())),
            'enrolled_count' => $this->whenCounted('enrollments'),
        ];
    }
}

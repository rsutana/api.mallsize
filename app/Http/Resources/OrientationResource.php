<?php

namespace App\Http\Resources;

use App\Models\Orientation;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Orientation
 */
class OrientationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'course_id' => $this->course_id,
            'course_name' => $this->course_id ? $this->course?->name : null,
            'details' => $this->details,
            'date' => $this->date->toDateString(),
            'time_label' => $this->time_label,
            'format' => $this->format,
            'format_label' => Lookups::label('orientation_formats', $this->format),
            'venue' => $this->venue,
            'is_social_linked' => $this->is_social_linked,
            'social_platform' => $this->social_platform,
            'social_platform_label' => Lookups::label('social_platforms', $this->social_platform),
            'social_url' => $this->social_url,
            'link_mode' => $this->link_mode,
            'link_mode_label' => Lookups::label('orientation_link_modes', $this->link_mode),
            'status' => $this->status,
            'status_label' => Lookups::label('post_statuses', $this->status),
            'is_notify_students' => $this->is_notify_students,
            'notified_at' => $this->notified_at?->toIso8601String(),
        ];
    }
}

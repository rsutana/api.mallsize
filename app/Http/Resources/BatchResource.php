<?php

namespace App\Http\Resources;

use App\Models\Batch;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Batch
 */
class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'name' => $this->name,
            'status' => $this->status,
            'status_label' => Lookups::label('batch_statuses', $this->status),
            'sets' => BatchSetResource::collection($this->whenLoaded('sets')),
        ];
    }
}

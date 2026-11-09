<?php

namespace App\Http\Resources;

use App\Models\Batch;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Batch
 */
class BatchCapacityResource extends JsonResource
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
            'status_label' => Lookups::label('batch_statuses', $this->status),
            ...SetCapacityResource::totals($this->sets),
            'sets' => SetCapacityResource::collection($this->sets),
        ];
    }
}

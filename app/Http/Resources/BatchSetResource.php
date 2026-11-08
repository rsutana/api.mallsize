<?php

namespace App\Http\Resources;

use App\Models\BatchSet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BatchSet
 */
class BatchSetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_id' => $this->batch_id,
            'name' => $this->name,
            'slots' => $this->slots,
            'slots_taken' => $this->slotsTaken(),
            'slots_left' => $this->slotsLeft(),
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->endDate()->toDateString(),
            'training_days' => $this->training_days,
            'time_label' => $this->time_label,
        ];
    }
}

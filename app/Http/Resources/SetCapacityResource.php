<?php

namespace App\Http\Resources;

use App\Models\BatchSet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One batch set's seat usage for the back-office overview.
 *
 * Expects `withSlotsTaken()` and an `enrolled_count` withCount on the set.
 *
 * @mixin BatchSet
 *
 * @property int|null $enrolled_count
 */
class SetCapacityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->endDate()->toDateString(),
            'training_days' => $this->training_days,
            'time_label' => $this->time_label,
            ...self::totals([$this->resource]),
        ];
    }

    /**
     * Seat totals across sets: slots, enrolled, pending holds, taken and left.
     *
     * @param  iterable<BatchSet>  $sets
     * @return array{slots: int, enrolled_count: int, pending_count: int, slots_taken: int, slots_left: int}
     */
    public static function totals(iterable $sets): array
    {
        $totals = ['slots' => 0, 'enrolled_count' => 0, 'pending_count' => 0, 'slots_taken' => 0, 'slots_left' => 0];

        foreach ($sets as $set) {
            $enrolled = (int) $set->getAttribute('enrolled_count');
            $taken = $set->slotsTaken();

            $totals['slots'] += $set->slots;
            $totals['enrolled_count'] += $enrolled;
            $totals['pending_count'] += max(0, $taken - $enrolled);
            $totals['slots_taken'] += $taken;
            $totals['slots_left'] += $set->slotsLeft();
        }

        return $totals;
    }
}

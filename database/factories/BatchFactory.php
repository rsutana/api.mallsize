<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Lookups\BatchStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => 'Batch '.fake()->numberBetween(1, 20).' — '.now()->addMonth()->format('F Y'),
            'status' => BatchStatus::OPEN,
            'sort_order' => 0,
        ];
    }
}

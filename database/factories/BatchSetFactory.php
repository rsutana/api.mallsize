<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\BatchSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchSet>
 */
class BatchSetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'name' => 'Set A',
            'slots' => 20,
            'start_date' => now()->addMonth()->toDateString(),
            'training_days' => 10,
            'time_label' => '8 AM – 5 PM',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lookups\CourseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Meat Processing – '.fake()->unique()->word(),
            'description' => fake()->sentence(),
            'fee' => 2_250_000,
            'min_down_payment' => 500_000,
            'status' => CourseStatus::OPEN,
            'sort_order' => 0,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => CourseStatus::CLOSED]);
    }
}

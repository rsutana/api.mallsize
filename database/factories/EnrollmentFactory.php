<?php

namespace Database\Factories;

use App\Models\Lookups\EnrollmentStatus;
use App\Models\BatchSet;
use App\Models\Enrollment;
use App\Models\Location;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'batch_set_id' => BatchSet::factory(),
            'batch_id' => fn (array $attributes) => BatchSet::query()->findOrFail($attributes['batch_set_id'])->batch_id,
            'course_id' => fn (array $attributes) => BatchSet::query()->with('batch')->findOrFail($attributes['batch_set_id'])->batch->course_id,
            'location_id' => Location::factory(),
            'fee' => fn (array $attributes) => BatchSet::query()->with('batch.course')->findOrFail($attributes['batch_set_id'])->batch->course->fee,
            'min_down_payment' => fn (array $attributes) => BatchSet::query()->with('batch.course')->findOrFail($attributes['batch_set_id'])->batch->course->min_down_payment,
            'status' => EnrollmentStatus::ENROLLED,
            'expires_at' => null,
            'enrolled_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::PENDING_PAYMENT,
            'expires_at' => now()->addMinutes(30),
            'enrolled_at' => null,
        ]);
    }
}

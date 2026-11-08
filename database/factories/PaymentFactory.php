<?php

namespace Database\Factories;

use App\Models\Lookups\PaymentMethod;
use App\Models\Lookups\PaymentStatus;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'reference_no' => 'MS-RC-'.fake()->unique()->numerify('#####'),
            'amount' => 500_000,
            'method' => PaymentMethod::GCASH,
            'status' => PaymentStatus::PAID,
            'provider' => 'fake',
            'provider_intent_id' => 'fake_'.fake()->uuid(),
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PaymentStatus::PENDING, 'paid_at' => null]);
    }
}

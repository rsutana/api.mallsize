<?php

namespace Database\Factories;

use App\Models\Lookups\EducationLevel;
use App\Models\Lookups\Sex;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'user_id' => User::factory()->state(['name' => "{$first} {$last}"]),
            'reference_no' => 'MS-'.now()->year.'-'.fake()->unique()->numerify('####'),
            'first_name' => $first,
            'middle_name' => null,
            'last_name' => $last,
            'birthdate' => fake()->dateTimeBetween('-45 years', '-18 years')->format('Y-m-d'),
            'sex' => fake()->randomElement([Sex::MALE, Sex::FEMALE]),
            'education' => fake()->randomElement([EducationLevel::HIGH_SCHOOL, EducationLevel::SENIOR_HIGH_SCHOOL, EducationLevel::COLLEGE]),
            'address' => fake()->streetAddress().', Davao City, Davao del Sur',
        ];
    }
}

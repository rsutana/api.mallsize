<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Lookups\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->lastName().' Meat Shop',
            'type' => fake()->randomElement([LocationType::PORK, LocationType::BEEF, LocationType::PORK_AND_BEEF]),
            'contact_person' => fake()->name(),
            'mobile' => '09'.fake()->numerify('## ### ####'),
            'email' => fake()->optional()->safeEmail(),
            'street' => 'Stall '.fake()->numberBetween(1, 40).', Public Market',
            'barangay' => fake()->randomElement(['Bankerohan', 'Toril', 'Poblacion', 'Visayan Village']),
            'city' => fake()->randomElement(['Davao City', 'Panabo City', 'Tagum City']),
            'province' => 'Davao del Sur',
            'zip' => '8000',
            'map_url' => null,
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}

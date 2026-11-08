<?php

namespace Database\Factories;

use App\Models\Lookups\OrientationFormat;
use App\Models\Lookups\OrientationLinkMode;
use App\Models\Lookups\PostStatus;
use App\Models\Orientation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Orientation>
 */
class OrientationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Pre-training orientation',
            'course_id' => null,
            'details' => 'Bring a valid ID, closed shoes and a notebook.',
            'date' => now()->addWeek()->toDateString(),
            'time_label' => '9:00 AM – 11:00 AM',
            'format' => OrientationFormat::ON_SITE,
            'venue' => 'Training room, MallSize Training Center',
            'is_social_linked' => false,
            'link_mode' => OrientationLinkMode::DETAILS,
            'status' => PostStatus::PUBLISHED,
            'is_notify_students' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PostStatus::DRAFT]);
    }
}
